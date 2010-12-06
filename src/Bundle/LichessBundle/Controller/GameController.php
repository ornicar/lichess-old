<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Form;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GameController extends Controller
{
    public function listCurrentAction()
    {
        $ids = $this->get('lichess.repository.game')->findRecentStartedGameIds(9);

        return $this->render('LichessBundle:Game:listCurrent.twig', array(
            'ids'         => $ids,
            'implodedIds' => implode(',', $ids),
            'nbGames'     => $this->get('lichess.repository.game')->getNbGames(),
            'nbMates'     => $this->get('lichess.repository.game')->getNbMates()
        ));
    }

    public function listCurrentInnerAction($ids)
    {
        return $this->render('LichessBundle:Game:listCurrentInner.twig', array(
            'games' => $this->get('lichess.repository.game')->findGamesByIds($ids)
        ));
    }

    public function listAllAction()
    {
        return $this->render('LichessBundle:Game:listAll.twig', array(
            'games'    => $this->get('lichess_service_game')->getRecentlyStarted($this->get('request')->query->get('page', 1), 10),
            'nbGames'  => $this->get('lichess.repository.game')->getNbGames(),
            'nbMates'  => $this->get('lichess.repository.game')->getNbMates(),
            'pagerUrl' => $this->generateUrl('lichess_list_all')
        ));
    }

    public function listCheckmateAction()
    {
        return $this->render('LichessBundle:Game:listMates.twig', array(
            'games'    => $this->get('lichess_service_game')->getRecentMates($this->get('request')->query->get('page', 1), 10),
            'nbGames'  => $this->get('lichess.repository.game')->getNbGames(),
            'nbMates'  => $this->get('lichess.repository.game')->getNbMates(),
            'pagerUrl' => $this->generateUrl('lichess_list_mates')
        ));
    }

    /**
     * Join a game and start it if new, or see it as a spectator
     */
    public function showAction($id)
    {
        $game = $this->findGame($id);

        if($this->get('request')->getMethod() === 'HEAD') {
            return $this->createResponse(sprintf('Game #%s', $id));
        }

        if($game->getIsStarted()) {
            return $this->forward('LichessBundle:Game:watch', array('id' => $id));
        }

        return $this->render('LichessBundle:Game:join.twig', array(
            'game'  => $game,
            'color' => $game->getCreator()->getOpponent()->getColor()
        ));
    }

    public function joinAction($id)
    {
        $game = $this->findGame($id);

        if($this->get('request')->getMethod() === 'HEAD') {
            return $this->createResponse(sprintf('Game #%s', $id));
        }

        if (!$this->get('lichess_service_game')->joinGame($game)) {
            return $this->redirect($this->generateUrl('lichess_game', array('id' => $id)));
        } else {
            return $this->redirect($this->generateUrl('lichess_player', array('id' => $game->getInvited()->getFullId())));
        }
    }

    public function watchAction($id)
    {
        $game = $this->findGame($id);
        $player = $game->getCreator();
        $analyser = new Analyser($game->getBoard());
        $isKingAttacked = $analyser->isKingAttacked($game->getTurnPlayer());
        if($isKingAttacked) {
            $checkSquareKey = $game->getTurnPlayer()->getKing()->getSquareKey();
        }
        else {
            $checkSquareKey = null;
        }
        $possibleMoves = ($player->isMyTurn() && $game->getIsPlayable()) ? 1 : null;

        return $this->render('LichessBundle:Player:watch.twig', array(
            'player'         => $player,
            'checkSquareKey' => $checkSquareKey,
            'possibleMoves'  => $possibleMoves
        ));
    }

    public function inviteFriendAction($color)
    {
        $isAuthenticated = $this->get('lichess.security.helper')->isAuthenticated();
        $config = new Form\FriendGameConfig();
        $config->fromArray($this->get('session')->get('lichess.game_config.friend', array()));
        if(!$isAuthenticated) {
            $config->mode = 0;
        }
        $formClass = 'Bundle\LichessBundle\Form\\'.($isAuthenticated ? 'FriendWithModeGameConfigForm' : 'FriendGameConfigForm');
        $form = new $formClass('config', $config, $this->get('validator'));
        if('POST' === $this->get('request')->getMethod()) {
            $form->bind($this->get('request')->request->get($form->getName()));
            if($form->isValid()) {
                $this->get('session')->set('lichess.game_config.friend', $config->toArray());

                $service = $this->get('lichess_service_game');
                $player = $service->createFriendGame($config, $color);

                return $this->redirect($this->generateUrl('lichess_wait_friend', array('id' => $player->getFullId())));
            }
        }

        return $this->render('LichessBundle:Game:inviteFriend.twig', array(
            'form'  => $form,
            'color' => $color
        ));
    }

    public function inviteAiAction($color)
    {
        $config = new Form\AiGameConfig();
        $config->fromArray($this->get('session')->get('lichess.game_config.ai', array()));
        $form = new Form\AiGameConfigForm('config', $config, $this->get('validator'));
        if('POST' === $this->get('request')->getMethod()) {
            $form->bind($this->get('request')->request->get($form->getName()));
            if($form->isValid()) {
                $this->get('session')->set('lichess.game_config.ai', $config->toArray());

                $service = $this->get('lichess_service_game');
                $player = $service->createAiGame($config, $color);

                return $this->redirect($this->generateUrl('lichess_player', array('id' => $player->getFullId())));
            }
        }

        return $this->render('LichessBundle:Game:inviteAi.twig', array(
            'form'  => $form,
            'color' => $color
        ));
    }

    public function inviteAnybodyAction($color)
    {
        if($this->get('request')->getMethod() == 'HEAD') {
            return $this->createResponse('Lichess play chess with anybody');
        }
        $isAuthenticated = $this->get('lichess.security.helper')->isAuthenticated();
        $config = new Form\AnybodyGameConfig();
        $config->fromArray($this->get('session')->get('lichess.game_config.anybody', array()));
        if(!$isAuthenticated) {
            $config->modes = array(0);
        }
        $formClass = 'Bundle\LichessBundle\Form\\'.($isAuthenticated ? 'AnybodyWithModesGameConfigForm' : 'AnybodyGameConfigForm');
        $form = new $formClass('config', $config, $this->get('validator'));
        if('POST' === $this->get('request')->getMethod()) {
            $form->bind($this->get('request')->request->get($form->getName()));
            if($form->isValid()) {
                $this->get('session')->set('lichess.game_config.anybody', $config->toArray());

                $service = $this->get('lichess_service_game');
                $game = $service->createInvitation($config, $color);

                if (!$game) {
                    return $this->inviteAnybodyAction($color);
                } elseif ($game->hasClock()) {
                    return $this->redirect($this->generateUrl('lichess_game', array('id' => $game->getId())));
                } else {
                    return $this->redirect($this->generateUrl('lichess_wait_anybody', array('id' => $game->getCreator()->getFullId())));
                }
            }
        }

        return $this->render('LichessBundle:Game:inviteAnybody.twig', array(
            'form'  => $form,
            'color' => $color
        ));
    }

    /**
     * Return the game for this id
     *
     * @param string $id
     * @return Game
     */
    protected function findGame($id)
    {
        $game = $this->get('lichess.repository.game')->findOneById($id);

        if(!$game) {
            throw new NotFoundHttpException('Can\'t find game '.$id);
        }

        return $game;
    }
}
