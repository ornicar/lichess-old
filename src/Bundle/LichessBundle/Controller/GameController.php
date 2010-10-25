<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Entities\Game;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Clock;
use Bundle\LichessBundle\Stack;
use Bundle\LichessBundle\Form;
use Bundle\LichessBundle\Persistence\QueueEntry;
use Bundle\LichessBundle\Zend\Paginator\Adapter\GameAdapter;
use Zend\Paginator\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GameController extends Controller
{
    public function listAction()
    {
        $hashes = $this['lichess_persistence']->findRecentGamesHashes(9);
        $hashes = implode(',', $hashes);
        $nbGames = $this['lichess_persistence']->getNbGames();

        return $this->render('LichessBundle:Game:list.php', array('hashes' => $hashes, 'nbGames' => $nbGames));
    }

    public function listInnerAction($hashes)
    {
        $hashes = explode(',', $hashes);
        $games = $this['lichess_persistence']->findGamesByHashes($hashes);

        return $this->render('LichessBundle:Game:listInner.php', array('games' => $games));
    }

    public function allAction()
    {
        $page = $this['request']->query->get('page', 1);
        $games = new Paginator(new GameAdapter($this['lichess_persistence']));
        $games->setCurrentPageNumber($page);
        $games->setItemCountPerPage(10);
        $games->setPageRange(10);

        return $this->render('LichessBundle:Game:all.php', array('games' => $games));
    }

    /**
     * Join a game and start it if new, or see it as a spectator
     */
    public function showAction($hash)
    {
        $game = $this->findGame($hash);

        if($game->getIsStarted()) {
            return $this->forward('LichessBundle:Game:watch', array('hash' => $hash));
        }

        if($this['request']->getMethod() == 'HEAD') {
            return $this->createResponse(sprintf('Game #%s', $hash));
        }

        return $this->render('LichessBundle:Game:join.php', array('game' => $game, 'color' => $game->getCreator()->getOpponent()->getColor()));
    }

    public function joinAction($hash)
    {
        $game = $this->findGame($hash);

        if($game->getIsStarted()) {
            $this['logger']->warn(sprintf('Game:join started game:%s', $game->getHash()));
            return $this->redirect($this->generateUrl('lichess_game', array('hash' => $hash)));
        }

        if($this['request']->getMethod() == 'HEAD') {
            $this['logger']->warn(sprintf('Game:join HEAD game:%s', $game->getHash()));
            return $this->createResponse(sprintf('Game #%s', $hash));
        }

        $game->start();
        $game->getCreator()->getStack()->addEvent(array(
            'type' => 'redirect',
            'url' => $this->generateUrl('lichess_player', array('hash' => $game->getCreator()->getFullHash()))
        ));
        $this['lichess_persistence']->save($game);
        $this['logger']->notice(sprintf('Game:join game:%s, variant:%s', $game->getHash(), $game->getVariantName()));
        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $game->getInvited()->getFullHash())));
    }

    public function watchAction($hash)
    {
        $game = $this->findGame($hash);
        $color = 'white';
        $player = $game->getPlayer($color);
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
        $config = new Form\FriendGameConfig($this['lichess_translator']);
        $config->fromArray($this['session']->get('lichess.game_config.friend', array()));
        $form = new Form\FriendGameConfigForm('config', $config, $this['validator']);
        if('POST' === $this['request']->getMethod()) {
            $form->bind($this['request']->request->get($form->getName()));
            if($form->isValid()) {
                $this['session']->set('lichess.game_config.friend', $config->toArray());
                $player = $this['lichess_generator']->createGameForPlayer($color, $config->variant);
                if($config->time) {
                    $clock = new Clock($config->time * 60);
                    $player->getGame()->setClock($clock);
                }
                $this['lichess_persistence']->save($player->getGame());
                $this['logger']->notice(sprintf('Game:inviteFriend create game:%s, variant:%s, time:%d', $player->getGame()->getHash(), $player->getGame()->getVariantName(), $config->time));
                return $this->redirect($this->generateUrl('lichess_wait_friend', array('hash' => $player->getFullHash())));
            }
        }

        return $this->render('LichessBundle:Game:inviteFriend.php', array('form' => $this['templating.form']->get($form), 'color' => $color));
    }

    public function inviteAiAction($color)
    {
        $player = $this['lichess_generator']->createGameForPlayer($color);
        $game = $player->getGame();
        $opponent = $player->getOpponent();
        $opponent->setIsAi(true);
        $opponent->setAiLevel(1);
        $game->start();

        if($player->isBlack()) {
            $manipulator = new Manipulator($game, new Stack());
            $manipulator->play($this->container->getLichessAiService()->move($game, $opponent->getAiLevel()));
        }
        $this['lichess_persistence']->save($game);
        $this['logger']->notice(sprintf('Game:inviteAi create game:%s, variant:%s', $game->getHash(), $game->getVariantName()));

        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $player->getFullHash())));
    }

    public function inviteAnybodyAction($color)
    {
        if($this['request']->getMethod() == 'HEAD') {
            return $this->createResponse('Lichess play chess with anybody');
        }
        $config = new Form\AnybodyGameConfig($this['lichess_translator']);
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
                    $game = $this['lichess_persistence']->find($result['game_hash']);
                    if(!$game) {
                        return $this->inviteAnybodyAction($color);
                    }
                    if(!$this['lichess_synchronizer']->isConnected($game->getCreator())) {
                        $this['lichess_persistence']->remove($game);
                        $this['logger']->notice(sprintf('Game:inviteAnybody remove game:%s', $game->getHash()));
                        return $this->inviteAnybodyAction($color);
                    }
                    $this['lichess_generator']->applyVariant($game, $result['variant']);
                    if($result['time']) {
                        $clock = new Clock($result['time'] * 60);
                        $game->setClock($clock);
                    }
                    $this['lichess_persistence']->save($game);
                    $this['logger']->notice(sprintf('Game:inviteAnybody join game:%s, variant:%s, time:%s', $game->getHash(), $game->getVariantName(), $result['time']));
                    return $this->redirect($this->generateUrl('lichess_game', array('hash' => $game->getHash())));
                }
                $game = $result['game'];
                $this['lichess_persistence']->save($game);
                $this['logger']->notice(sprintf('Game:inviteAnybody queue game:%s, variant:%s, time:%s', $game->getHash(), implode(',', $config->variants), implode(',', $config->times)));
                return $this->redirect($this->generateUrl('lichess_wait_anybody', array('hash' => $game->getCreator()->getFullHash())));
            }
        }

        return $this->render('LichessBundle:Game:inviteAnybody.php', array('form' => $this['templating.form']->get($form), 'color' => $color));
    }

    /**
     * Return the game for this hash
     *
     * @param string $hash
     * @return Game
     */
    protected function findGame($hash)
    {
        $game = $this['lichess_persistence']->find($hash);

        if(!$game) {
            throw new NotFoundHttpException('Can\'t find game '.$hash);
        }

        return $game;
    }
}
