<?php

namespace Lichess\OpeningBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Lichess\OpeningBundle\Form\HookFormType;
use Lichess\OpeningBundle\Document\Hook;
use Lichess\OpeningBundle\Document\Message;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Lichess\OpeningBundle\Config\GameConfigView;
use Lichess\OpeningBundle\Config\GameConfig;
use Bundle\LichessBundle\Document\Clock;
use Symfony\Component\HttpFoundation\Response;
use Application\UserBundle\Document\User;
use Bundle\LichessBundle\Chess\GameEvent;

class HookController extends Controller
{
    public function indexAction()
    {
        $auth = $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') ? '1' : '0';

        return $this->render('LichessOpeningBundle::index.html.twig', array(
            'auth' => $auth,
            'preload' => $this->get('lila')->lobbyPreload($auth)
        ));
    }

    public function newAction()
    {
        $request = $this->get('request');
        $form = $this->get('lichess.form.manager')->createHookForm();
        if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $config = $form->getData();
                $hook = new Hook();
                $hook->fromArray($config->toArray());
                if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                    $hook->setUser($this->get('security.context')->getToken()->getUser());
                }
                $this->get('lichess.config.persistence')->saveConfigFor('hook', $config->toArray());
                $this->get('doctrine.odm.mongodb.document_manager')->persist($hook);
                $this->get('doctrine.odm.mongodb.document_manager')->flush();
                $this->get('lila')->lobbyCreate($hook->getOwnerId());

                return new RedirectResponse($this->generateUrl('lichess_hook', array('id' => $hook->getOwnerId())));
            } else {
                return new RedirectResponse($this->generateUrl('lichess_homepage'));
            }
        } elseif (!$request->isXmlHttpRequest() && $this->container->getParameter('kernel.environment') !== 'test') {
            return new RedirectResponse($this->generateUrl('lichess_homepage').'#hook');
        }

        return $this->render('LichessOpeningBundle:Config:hook.html.twig', array('form' => $form->createView(), 'config' => $form->getData()));
    }

    public function pollResultAction($myHookId, $state, $messageId, $entryId, $auth)
    {
        return $this->renderJson(array(
            'state' => $newState,
            'pool' => $this->get('lichess_opening.hooks_renderer')->render($auth, $myHookId),
            'chat' => $messageId !== false ? $this->get('lichess_opening.messages_renderer')->render($messageId) : null,
            'timeline' => $this->get('lichess_opening.timeline_renderer')->render($entryId)
        ));
    }

    public function hookAction($id)
    {
        $hook = $this->get('lichess_opening.hook_repository')->findOneByOwnerId($id);
        if (!$hook) {
            return new RedirectResponse($this->generateUrl('lichess_homepage'));
        }
        $auth = $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') ? '1' : '0';

        return $this->render('LichessOpeningBundle:Hook:hook.html.twig', array(
            'auth' => $auth,
            'myHookId' => $id,
            'preload' => $this->get('lila')->lobbyPreload($auth, $id)
        ));
    }

    public function messageAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();

        if ($user instanceof User && $user->canSeeChat()) {
          $text = trim($this->get('request')->get('message'));
          if ($message = $this->get('lichess_opening.messenger')->send($user, $text)) {
              $this->get('doctrine.odm.mongodb.document_manager')->flush();
              $this->get('lila')->lobbyMessage();
          }
        }

        return new Response('ok');
    }

    public function cancelAction($id)
    {
        $hook = $this->get('lichess_opening.hook_repository')->findOneByOwnerId($id);
        if ($hook) {
            $this->get('lila')->lobbyRemove($hook->getId());
        }

        return new RedirectResponse($this->generateUrl('lichess_homepage'));
    }

    public function joinAction($id)
    {
        $hook = $this->get('lichess_opening.hook_repository')->findOneById($id);
        $myHookId = $this->get('request')->query->get('cancel');
        // hook is not available anymore
        // hook elo range does not let me in
        if (!$hook || $hook->isMatch() || !$hook->userCanJoin($this->get('security.context')->getToken()->getUser())) {
            if ($myHookId) {
                return new RedirectResponse($this->generateUrl('lichess_hook', array('id' => $myHookId)));
            }
            return new RedirectResponse($this->generateUrl('lichess_homepage'));
        }
        // if I also have a hook, cancel it
        if ($myHookId) {
            if ($myHook = $this->get('lichess_opening.hook_repository')->findOneByOwnerId($myHookId)) {
                $this->get('doctrine.odm.mongodb.document_manager')->remove($myHook);
            }
        }
        $config = new GameConfig();
        $config->fromArray($hook->toArray());
        $color = $config->resolveColor();
        $opponent = $this->get('lichess.generator')->createGameForPlayer($color, $config->getVariant());
        $opponent->setUser($hook->getUser());
        $player = $opponent->getOpponent();
        $this->get('lichess.blamer.player')->blame($player);
        $game = $player->getGame();
        if($config->getClock()) {
            $clock = new Clock($config->getTime() * 60, $config->getIncrement());
            $game->setClock($clock);
        }
        $game->setIsRated($config->getMode());
        $this->get('lichess.starter.game')->start($game);
        $hook->setGame($game);
        $this->get('doctrine.odm.mongodb.document_manager')->persist($game);
        $this->get('doctrine.odm.mongodb.document_manager')->flush(array('safe' => true));
        $this->get('lila')->lobbyJoin($player);

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $player->getFullId())));
    }

    protected function renderJson($data)
    {
        return new Response(json_encode($data), 200, array('Content-Type' => 'application/json'));
    }
}
