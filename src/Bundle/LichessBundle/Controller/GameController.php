<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Document\Clock;
use Bundle\LichessBundle\Document\Stack;
use Bundle\LichessBundle\Form;
use Bundle\LichessBundle\Persistence\QueueEntry;
use Bundle\LichessBundle\Zend\Paginator\Adapter\GameAdapter;
use Zend\Paginator\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GameController extends Controller
{
    public function listAction()
    {
        $ides = $this['lichess_persistence']->findRecentGamesIds(9);
        $ides = implode(',', $ides);
        $nbGames = $this['lichess_persistence']->getNbGames();
        $nbMates = $this['lichess_persistence']->getNbMates();

        return $this->render('LichessBundle:Game:listCurrent.php', compact('ides', 'nbGames', 'nbMates'));
    }

    public function listInnerAction($ides)
    {
        $ides = explode(',', $ides);
        $games = $this['lichess_persistence']->findGamesByIds($ides);

        return $this->render('LichessBundle:Game:listCurrentInner.php', array('games' => $games));
    }

    public function listAllAction()
    {
        $page = $this['request']->query->get('page', 1);
        $games = new Paginator(new GameAdapter($this['lichess_persistence'], GameAdapter::STARTED));
        $games->setCurrentPageNumber($page);
        $games->setItemCountPerPage(10);
        $games->setPageRange(10);

        $nbGames = $this['lichess_persistence']->getNbGames();
        $nbMates = $this['lichess_persistence']->getNbMates();
        $pagerUrl = $this->generateUrl('lichess_list_all');
        return $this->render('LichessBundle:Game:listAll.php', compact('games', 'nbGames', 'nbMates', 'pagerUrl'));
    }

    public function listCheckmateAction()
    {
        $page = $this['request']->query->get('page', 1);
        $games = new Paginator(new GameAdapter($this['lichess_persistence'], GameAdapter::MATE));
        $games->setCurrentPageNumber($page);
        $games->setItemCountPerPage(10);
        $games->setPageRange(10);

        $nbGames = $this['lichess_persistence']->getNbGames();
        $nbMates = $this['lichess_persistence']->getNbMates();
        $pagerUrl = $this->generateUrl('lichess_list_mates');
        return $this->render('LichessBundle:Game:listMates.php', compact('games', 'nbGames', 'nbMates', 'pagerUrl'));
    }

    /**
     * Join a game and start it if new, or see it as a spectator
     */
    public function showAction($id)
    {
        $game = $this->findGame($id);

        if($this['request']->getMethod() == 'HEAD') {
            return $this->createResponse(sprintf('Game #%s', $id));
        }

        if($game->getIsStarted()) {
            return $this->forward('LichessBundle:Game:watch', array('id' => $id));
        }

        return $this->render('LichessBundle:Game:join.php', array('game' => $game, 'color' => $game->getCreator()->getOpponent()->getColor()));
    }

    public function joinAction($id)
    {
        $game = $this->findGame($id);

        if($this['request']->getMethod() == 'HEAD') {
            $this['logger']->warn(sprintf('Game:join HEAD game:%s', $game->getId()));
            return $this->createResponse(sprintf('Game #%s', $id));
        }

        if($game->getIsStarted()) {
            $this['logger']->warn(sprintf('Game:join started game:%s', $game->getId()));
            return $this->redirect($this->generateUrl('lichess_game', array('id' => $id)));
        }

        $game->start();
        $game->getCreator()->getStack()->addEvent(array(
            'type' => 'redirect',
            'url' => $this->generateUrl('lichess_player', array('id' => $game->getCreator()->getFullId()))
        ));
        $this['lichess_persistence']->save($game);
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

        return $this->render('LichessBundle:Game:watch.php', array('game' => $game, 'player' => $player, 'checkSquareKey' => $checkSquareKey, 'parameters' => $this->container->getParameterBag()->all(), 'possibleMoves' => $possibleMoves));
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

        return $this->render('LichessBundle:Game:inviteFriend.php', array('form' => $this['templating.form']->get($form), 'color' => $color));
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
                $this['doctrine.odm.mongodb.document_manager']->persist($game);
                $this['doctrine.odm.mongodb.document_manager']->flush();
                $this['logger']->notice(sprintf('Game:inviteAi create game:%s, variant:%s', $game->getId(), $game->getVariantName()));

                return $this->redirect($this->generateUrl('lichess_player', array('id' => $player->getFullId())));
            }
        }

        return $this->render('LichessBundle:Game:inviteAi.php', array('form' => $this['templating.form']->get($form), 'color' => $color));
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
                $queueEntry = new QueueEntry($config->times, $config->variants, $this['session']->get('lichess.user_id'));
                $queue = $this['lichess_queue'];
                $result = $queue->add($queueEntry, $color);
                if($result['status'] === $queue::FOUND) {
                    $game = $this['lichess_persistence']->find($result['game_id']);
                    if(!$game) {
                        return $this->inviteAnybodyAction($color);
                    }
                    if(!$this['lichess_synchronizer']->isConnected($game->getCreator())) {
                        $this['lichess_persistence']->remove($game);
                        $this['logger']->notice(sprintf('Game:inviteAnybody remove game:%s', $game->getId()));
                        return $this->inviteAnybodyAction($color);
                    }
                    $this['lichess_generator']->applyVariant($game, $result['variant']);
                    if($result['time']) {
                        $clock = new Clock($result['time'] * 60);
                        $game->setClock($clock);
                    }
                    $this['lichess_persistence']->save($game);
                    $this['logger']->notice(sprintf('Game:inviteAnybody join game:%s, variant:%s, time:%s', $game->getId(), $game->getVariantName(), $result['time']));
                    return $this->redirect($this->generateUrl('lichess_game', array('id' => $game->getId())));
                }
                $game = $result['game'];
                $this['lichess_persistence']->save($game);
                $this['logger']->notice(sprintf('Game:inviteAnybody queue game:%s, variant:%s, time:%s', $game->getId(), implode(',', $config->getVariantNames()), implode(',', $config->times)));
                return $this->redirect($this->generateUrl('lichess_wait_anybody', array('id' => $game->getCreator()->getFullId())));
            }
        }

        return $this->render('LichessBundle:Game:inviteAnybody.php', array('form' => $this['templating.form']->get($form), 'color' => $color));
    }

    /**
     * Return the game for this id
     *
     * @param string $id
     * @return Game
     */
    protected function findGame($id)
    {
        $game = $this['lichess_persistence']->find($id);

        if(!$game) {
            throw new NotFoundHttpException('Can\'t find game '.$id);
        }

        return $game;
    }
}
