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
        return $this->render('LichessOpeningBundle::index.html.twig', array(
            'auth' => $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') ? '1' : '0'
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
                $this->get('lichess_opening.memory')->setAlive($hook);
                $this->get('lichess_opening.memory')->incrementState();

                return new RedirectResponse($this->generateUrl('lichess_hook', array('id' => $hook->getOwnerId())));
            } else {
                return new RedirectResponse($this->generateUrl('lichess_homepage'));
            }
        } elseif (!$request->isXmlHttpRequest() && $this->container->getParameter('kernel.environment') !== 'test') {
            return new RedirectResponse($this->generateUrl('lichess_homepage').'#hook');
        }

        return $this->render('LichessOpeningBundle:Config:hook.html.twig', array('form' => $form->createView(), 'config' => $form->getData()));
    }

    public function pollAction($myHookId)
    {
        $request = $this->get('request');
        $state = $request->query->get('state');
        $messageId = $request->query->get('messageId', false);
        $entryId = $request->query->get('entryId');
        $auth = $request->query->get('auth');
        $this->get('lichess_opening.http_push')->poll($state, $messageId, $entryId);

        return $this->pollResultAction($myHookId, $state, $messageId, $entryId, $auth);
    }

    public function pollResultAction($myHookId, $state, $messageId, $entryId, $auth)
    {
        if ($myHookId) {
            $myHookId = (string) $myHookId; // convert twig weirdness
            $myHook = $this->get('lichess_opening.hook_repository')->findOneByOwnerId($myHookId);
            if (!$myHook) {
                return $this->renderJson(array('redirect' => '/'));
            }
            if ($game = $myHook->getGame()) {
                $this->get('doctrine.odm.mongodb.document_manager')->remove($myHook);
                $this->get('doctrine.odm.mongodb.document_manager')->flush();
                $this->get('lichess_opening.memory')->incrementState();

                return $this->renderJson(array('redirect' => $game->getCreator()->getFullId()));
            }
        }
        $newState = $this->get('lichess_opening.memory')->getState();

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
        if ($game = $hook->getGame()) {
            $this->get('doctrine.odm.mongodb.document_manager')->remove($hook);
            $this->get('doctrine.odm.mongodb.document_manager')->flush();
            $this->get('lichess_opening.memory')->incrementState();

            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $game->getCreator()->getFullId())));
        }
        $this->get('lichess_opening.memory')->setAlive($hook);
        $auth = $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') ? '1' : '0';
        $newState = $this->get('lichess_opening.memory')->getState();

        return $this->render('LichessOpeningBundle:Hook:hook.html.twig', array(
            'state' => $newState,
            'pool' => $this->get('lichess_opening.hooks_renderer')->render($auth, $id),
            'auth' => $auth,
            'myHookId' => $id
        ));
    }

    public function messageAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();

        if ($user instanceof User && $user->canSeeChat()) {
          $text = trim($this->get('request')->get('message'));
          if ($message = $this->get('lichess_opening.messenger')->send($user, $text)) {
              $this->get('doctrine.odm.mongodb.document_manager')->flush();
              $this->get('lichess_opening.memory')->setMessageId($message->getId());
          }
        }

        return new Response('ok');
    }

    public function cancelAction($id)
    {
        $hook = $this->get('lichess_opening.hook_repository')->findOneByOwnerId($id);
        if ($hook) {
            $this->get('doctrine.odm.mongodb.document_manager')->remove($hook);
            $this->get('doctrine.odm.mongodb.document_manager')->flush();
            $this->get('lichess_opening.memory')->incrementState();
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
        $entry = $this->get('lichess_opening.bot')->onStart($game);
        $this->get('doctrine.odm.mongodb.document_manager')->flush(array('safe' => true));
        $this->get('lila')->lobbyJoin($player);
        if ($entry) {
            $this->get('lichess_opening.memory')->setEntryId($entry->getId());
        }
        $this->get('lichess_opening.memory')->incrementState();

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $player->getFullId())));
    }

    protected function renderJson($data)
    {
        return new Response(json_encode($data), 200, array('Content-Type' => 'application/json'));
    }
}
