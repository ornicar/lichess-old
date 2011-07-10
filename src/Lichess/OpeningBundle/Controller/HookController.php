<?php

namespace Lichess\OpeningBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Lichess\OpeningBundle\Form\HookFormType;
use Lichess\OpeningBundle\Document\Hook;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Lichess\OpeningBundle\Config\GameConfigView;
use Lichess\OpeningBundle\Config\GameConfig;
use Bundle\LichessBundle\Document\Clock;
use Symfony\Component\HttpFoundation\Response;

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
                $this->get('lichess.logger')->warn($hook, 'Hook::new hook created');

                return new RedirectResponse($this->generateUrl('lichess_hook', array('id' => $hook->getOwnerId())));
            } else {
                return new RedirectResponse($this->generateUrl('lichess_homepage'));
            }
        } elseif (!$request->isXmlHttpRequest()) {
            return new RedirectResponse($this->generateUrl('lichess_homepage').'#hook');
        }

        return $this->render('LichessOpeningBundle:Config:hook.html.twig', array('form' => $form->createView(), 'config' => $form->getData()));
    }

    public function hookAction($id)
    {
        $hook = $this->get('lichess_opening.hook_repository')->findOneByOwnerId($id);
        if (!$hook) {
            $this->get('lichess.logger')->warn(new Hook(), 'Hook::hook hook has disappeared, redirect to homepage');
            return new RedirectResponse($this->generateUrl('lichess_homepage'));
        }
        if ($game = $hook->getGame()) {
            $this->get('lichess.logger')->warn($hook, 'Hook::poll hook biten! redirect to game '.$game->getCreator()->getFullId());
            $this->get('doctrine.odm.mongodb.document_manager')->remove($hook);
            $this->get('doctrine.odm.mongodb.document_manager')->flush();
            $this->get('lichess_opening.memory')->incrementState();

            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $game->getCreator()->getFullId())));
        }
        $this->get('lichess_opening.memory')->setAlive($hook);
        $auth = $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') ? '1' : '0';

        return $this->render('LichessOpeningBundle:Hook:hook.html.twig', array(
            'data' => $this->get('lichess_opening.hooks_renderer')->render($auth, $id),
            'auth' => $auth,
            'myHookId' => $id
        ));
    }

    public function cancelAction($id)
    {
        $hook = $this->get('lichess_opening.hook_repository')->findOneByOwnerId($id);
        if ($hook) {
            $this->get('lichess.logger')->warn($hook, 'Hook::cancel');
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
        if (!$hook || $hook->isMatch()) {
            if ($myHookId) {
                $this->get('lichess.logger')->warn(new Hook(), 'Hook::join not available anymore, redirect to my own hook');
                return new RedirectResponse($this->generateUrl('lichess_hook', array('id' => $myHookId)));
            }
            $this->get('lichess.logger')->warn(new Hook(), 'Hook::join not available anymore, redirect to homepage');
            return new RedirectResponse($this->generateUrl('lichess_homepage'));
        }
        $this->get('lichess.logger')->warn($hook, 'Hook::join');
        // if I also have a hook, cancel it
        if ($myHookId) {
            if ($myHook = $this->get('lichess_opening.hook_repository')->findOneByOwnerId($myHookId)) {
                $this->get('lichess.logger')->warn($hook, 'Hook::join remove my own hook');
                $this->get('doctrine.odm.mongodb.document_manager')->remove($myHook);
            }
        }
        $config = new GameConfig();
        $config->fromArray($hook->toArray());
        $color = $config->resolveColor();
        $opponent = $this->get('lichess.generator')->createGameForPlayer($color, $config->getVariant());
        $opponent->setUser($hook->getUser());
        $this->get('lichess.memory')->setAlive($opponent);
        $player = $opponent->getOpponent();
        $this->get('lichess.blamer.player')->blame($player);
        $this->get('lichess.memory')->setAlive($player);
        $game = $player->getGame();
        if($config->getClock()) {
            $clock = new Clock($config->getTime() * 60, $config->getIncrement());
            $game->setClock($clock);
        }
        $game->setIsRated($config->getMode());
        $game->start();
        $hook->setGame($game);
        $this->get('doctrine.odm.mongodb.document_manager')->persist($game);
        $this->get('doctrine.odm.mongodb.document_manager')->flush(array('safe' => true));
        $this->get('lichess_opening.memory')->incrementState();
        $this->get('lichess.logger')->warn($hook, 'Hook::join redirect to game '.$player->getFullId());

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $player->getFullId())));
    }

    public function pollAction($myHookId)
    {
        $request = $this->get('request');
        $newState = $this->get('lichess_opening.http_push')->poll($request->query->get('state'));

        if ($myHookId) {
            $myHook = $this->get('lichess_opening.hook_repository')->findOneByOwnerId($myHookId);
            if (!$myHook) {
                $this->get('lichess.logger')->warn(new Hook(), 'Hook::poll hook has disappeared, redirect to homepage');

                return $this->renderJson(array('redirect' => $this->generateUrl('lichess_homepage')));
            }
            if ($game = $myHook->getGame()) {
                $this->get('lichess.logger')->warn($myHook, 'Hook::poll hook biten! redirect to game '.$game->getCreator()->getFullId());
                $this->get('doctrine.odm.mongodb.document_manager')->remove($myHook);
                $this->get('doctrine.odm.mongodb.document_manager')->flush();
                $this->get('lichess_opening.memory')->incrementState();

                return $this->renderJson(array('redirect' => $this->generateUrl('lichess_player', array('id' => $game->getCreator()->getFullId()))));
            }
            $this->get('lichess_opening.memory')->setAlive($myHook);
        } else {
            $myHook = null;
        }

        return $this->renderJson($this->get('lichess_opening.hooks_renderer')->render($request->query->get('auth'), $myHookId));
    }

    protected function renderJson($data)
    {
        return new Response(json_encode($data), 200, array('Content-Type' => 'application/json'));
    }
}
