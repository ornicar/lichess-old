<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Document\Clock;
use Bundle\LichessBundle\Document\Stack;
use Bundle\LichessBundle\Persistence\QueueEntry;
use Bundle\LichessBundle\Form;
use ZendPaginatorAdapter\DoctrineMongoDBAdapter;
use Zend\Paginator\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GameController extends Controller
{
    public function listCurrentAction()
    {
        $ids = $this['lichess.repository.game']->findRecentStartedGameIds(9);

        return $this->render('LichessBundle:Game:listCurrent.twig', array(
            'ids'         => $ids,
            'implodedIds' => implode(',', $ids),
            'nbGames'     => $this['lichess.repository.game']->getNbGames(),
            'nbMates'     => $this['lichess.repository.game']->getNbMates()
        ));
    }

    public function listCurrentInnerAction($ids)
    {
        return $this->render('LichessBundle:Game:listCurrentInner.twig', array(
            'games' => $this['lichess.repository.game']->findGamesByIds($ids)
        ));
    }

    public function listAllAction()
    {
        $query = $this['lichess.repository.game']->createRecentStartedOrFinishedQuery();

        return $this->render('LichessBundle:Game:listAll.twig', array(
            'games'    => $this->createPaginatorForQuery($query),
            'nbGames'  => $this['lichess.repository.game']->getNbGames(),
            'nbMates'  => $this['lichess.repository.game']->getNbMates(),
            'pagerUrl' => $this->generateUrl('lichess_list_all')
        ));
    }

    public function listCheckmateAction()
    {
        $query = $this['lichess.repository.game']->createRecentMateQuery();

        return $this->render('LichessBundle:Game:listMates.twig', array(
            'games'    => $this->createPaginatorForQuery($query),
            'nbGames'  => $this['lichess.repository.game']->getNbGames(),
            'nbMates'  => $this['lichess.repository.game']->getNbMates(),
            'pagerUrl' => $this->generateUrl('lichess_list_mates')
        ));
    }

    /**
     * Join a game and start it if new, or see it as a spectator
     */
    public function showAction($id)
    {
        $game = $this->findGame($id);

        if($this['request']->getMethod() === 'HEAD') {
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

        if($this['request']->getMethod() === 'HEAD') {
            return $this->createResponse(sprintf('Game #%s', $id));
        }

        if($game->getIsStarted()) {
            $this['logger']->warn(sprintf('Game:join started game:%s', $game->getId()));
            return $this->redirect($this->generateUrl('lichess_game', array('id' => $id)));
        }

        $game->start();
        $game->getCreator()->addEventToStack(array(
            'type' => 'redirect',
            'url'  => $this->generateUrl('lichess_player', array('id' => $game->getCreator()->getFullId()))
        ));
        $this['lichess.object_manager']->flush();
        $this['logger']->notice(sprintf('Game:join game:%s, variant:%s, time:%d', $game->getId(), $game->getVariantName(), $game->getClockMinutes()));
        return $this->redirect($this->generateUrl('lichess_player', array('id' => $game->getInvited()->getFullId())));
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
        $possibleMoves = ($player->isMyTurn() && !$game->getIsFinished()) ? 1 : null;

        return $this->render('LichessBundle:Game:watch.twig', array(
            'game'           => $game,
            'player'         => $player,
            'checkSquareKey' => $checkSquareKey,
            'parameters'     => $this->container->getParameterBag()->all(),
            'possibleMoves'  => $possibleMoves
        ));
    }

    public function inviteFriendAction($color)
    {
        $config = new Form\FriendGameConfig();
        $config->fromArray($this['session']->get('lichess.game_config.friend', array()));
        $form = new Form\FriendGameConfigForm('config', $config, $this['validator']);
        if('POST' === $this['request']->getMethod()) {
            $form->bind($this['request']->request->get($form->getName()));
            if($form->isValid()) {
                $this['session']->set('lichess.game_config.friend', $config->toArray());
                $player = $this['lichess_generator']->createGameForPlayer($color, $config->variant);
                $game = $player->getGame();
                if($config->time) {
                    $clock = new Clock($config->time * 60);
                    $game->setClock($clock);
                }
                $this['lichess.object_manager']->persist($game);
                $this['lichess.object_manager']->flush();
                $this['logger']->notice(sprintf('Game:inviteFriend create game:%s, variant:%s, time:%d', $game->getId(), $game->getVariantName(), $config->time));
                return $this->redirect($this->generateUrl('lichess_wait_friend', array('id' => $player->getFullId())));
            }
        }

        return $this->render('LichessBundle:Game:inviteFriend.twig', array(
            'form'  => $this['templating.form']->get($form),
            'color' => $color
        ));
    }

    public function inviteAiAction($color)
    {
        $config = new Form\AiGameConfig();
        $config->fromArray($this['session']->get('lichess.game_config.ai', array()));
        $form = new Form\AiGameConfigForm('config', $config, $this['validator']);
        if('POST' === $this['request']->getMethod()) {
            $form->bind($this['request']->request->get($form->getName()));
            if($form->isValid()) {
                $this['session']->set('lichess.game_config.ai', $config->toArray());
                $player = $this['lichess_generator']->createGameForPlayer($color, $config->variant);
                $game = $player->getGame();
                $opponent = $player->getOpponent();
                $opponent->setIsAi(true);
                $opponent->setAiLevel(1);
                $game->start();

                if($player->isBlack()) {
                    $manipulator = new Manipulator($game, new Stack());
                    $manipulator->play($this['lichess_ai']->move($game, $opponent->getAiLevel()));
                }
                $this['lichess.object_manager']->persist($game);
                $this['lichess.object_manager']->flush();
                $this['logger']->notice(sprintf('Game:inviteAi create game:%s, variant:%s', $game->getId(), $game->getVariantName()));

                return $this->redirect($this->generateUrl('lichess_player', array('id' => $player->getFullId())));
            }
        }

        return $this->render('LichessBundle:Game:inviteAi.twig', array(
            'form'  => $this['templating.form']->get($form),
            'color' => $color
        ));
    }

    public function inviteAnybodyAction($color)
    {
        if($this['request']->getMethod() == 'HEAD') {
            return $this->createResponse('Lichess play chess with anybody');
        }
        $config = new Form\AnybodyGameConfig();
        $config->fromArray($this['session']->get('lichess.game_config.anybody', array()));
        $form = new Form\AnybodyGameConfigForm('config', $config, $this['validator']);
        if('POST' === $this['request']->getMethod()) {
            $form->bind($this['request']->request->get($form->getName()));
            if($form->isValid()) {
                $this['session']->set('lichess.game_config.anybody', $config->toArray());
                $queue = $this['lichess.seek_queue'];
                $result = $queue->add($config->variants, $config->times, $this['session']->get('lichess.session_id'), $color);
                $game = $result['game'];
                if(!$game) {
                    return $this->inviteAnybodyAction($color);
                }
                if($result['status'] === $queue::FOUND) {
                    if(!$this['lichess_synchronizer']->isConnected($game->getCreator())) {
                        $this['lichess.object_manager']->remove($game);
                        $this['lichess.object_manager']->flush();
                        $this['logger']->notice(sprintf('Game:inviteAnybody remove game:%s', $game->getId()));
                        return $this->inviteAnybodyAction($color);
                    }
                    $this['logger']->notice(sprintf('Game:inviteAnybody join game:%s, variant:%s, time:%s', $game->getId(), $game->getVariantName(), $game->getClockName()));
                    return $this->redirect($this->generateUrl('lichess_game', array('id' => $game->getId())));
                }
                $this['logger']->notice(sprintf('Game:inviteAnybody queue game:%s, variant:%s, time:%s', $game->getId(), implode(',', $config->getVariantNames()), implode(',', $config->times)));
                return $this->redirect($this->generateUrl('lichess_wait_anybody', array('id' => $game->getCreator()->getFullId())));
            }
        }

        return $this->render('LichessBundle:Game:inviteAnybody.twig', array(
            'form'  => $this['templating.form']->get($form),
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
        $game = $this['lichess.repository.game']->findOneById($id);

        if(!$game) {
            throw new NotFoundHttpException('Can\'t find game '.$id);
        }

        return $game;
    }

    protected function createPaginatorForQuery($query)
    {
        $games = new Paginator(new DoctrineMongoDBAdapter($query));
        $games->setCurrentPageNumber($this['request']->query->get('page', 1));
        $games->setItemCountPerPage(10);
        $games->setPageRange(10);

        return $games;
    }
}
