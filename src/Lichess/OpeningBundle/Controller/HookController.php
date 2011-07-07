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
        $form = $this->get('lichess.form.manager')->createHookForm();
        if ($this->get('request')->getMethod() === 'POST') {
            $form->bindRequest($this->get('request'));
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
                $this->get('lichess_opening.sync_memory')->setAlive($hook);

                return new RedirectResponse($this->generateUrl('lichess_hook', array('id' => $hook->getOwnerId())));
            } else {
                return new RedirectResponse($this->generateUrl('lichess_homepage'));
            }
        }

        return $this->render('LichessOpeningBundle:Config:hook.html.twig', array('form' => $form->createView(), 'config' => $form->getData()));
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

            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $game->getCreator()->getFullId())));
        }
        $this->get('lichess_opening.sync_memory')->setAlive($hook);
        $config = new GameConfigView($hook->toArray());
        $hooks = $this->get('lichess_opening.hook_repository')->findAllOpen()->toArray();

        return $this->render('LichessOpeningBundle:Hook:hook.html.twig', array(
            'auth' => $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') ? '1' : '0',
            'hook' => $hook,
            'config' => $config,
            'hooks' => $hooks
        ));
    }

    public function cancelAction($id)
    {
        $hook = $this->get('lichess_opening.hook_repository')->findOneByOwnerId($id);
        if ($hook) {
            $this->get('doctrine.odm.mongodb.document_manager')->remove($hook);
            $this->get('doctrine.odm.mongodb.document_manager')->flush();
        }

        return new RedirectResponse($this->generateUrl('lichess_homepage'));
    }

    public function joinAction($id)
    {
        $hook = $this->get('lichess_opening.hook_repository')->findOneById($id);
        if (!$hook || $hook->isMatch()) {
            return new RedirectResponse($this->generateUrl('lichess_homepage'));
        }
        // if I also have a hook, cancel it
        if ($myHookId = $this->get('request')->query->get('cancel')) {
            if ($myHook = $this->get('lichess_opening.hook_repository')->findOneByOwnerId($myHookId)) {
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

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $player->getFullId())));
    }

    public function pollAction($auth, $id)
    {
        if ($id) {
            $myHook = $this->get('lichess_opening.hook_repository')->findOneByOwnerId($id);
            if (!$myHook) {
                return new Response($this->generateUrl('lichess_homepage'));
            }
            if ($game = $myHook->getGame()) {
                $this->get('doctrine.odm.mongodb.document_manager')->remove($myHook);
                $this->get('doctrine.odm.mongodb.document_manager')->flush();

                return new Response($this->generateUrl('lichess_player', array('id' => $game->getCreator()->getFullId())));
            }
            $this->get('lichess_opening.sync_memory')->setAlive($myHook);
        } else {
            $myHook = null;
        }
        if ($auth == 1) {
            $hooks = $this->get('lichess_opening.hook_repository')->findAllOpen()->toArray();
        } else {
            $hooks = $this->get('lichess_opening.hook_repository')->findAllOpenCasual()->toArray();
        }

        return $this->render('LichessOpeningBundle:Hook:list.html.twig', array('hooks' => $hooks, 'myHook' => $myHook));
    }
}
