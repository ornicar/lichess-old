<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Stack;
use Bundle\LichessBundle\Ai\Crafty;
use Bundle\LichessBundle\Ai\Stupid;
use Bundle\LichessBundle\Entities\Player;
use Bundle\LichessBundle\Entities\Game;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;

class PlayerController extends Controller
{

    public function rematchAction($hash)
    {
        $player = $this->findPlayer($hash);
        $game = $player->getGame();

        if($nextHash = $game->getNext()) {
            $nextGame = $this->getPersistence()->find($nextHash);
            $nextGame->setRoom(clone $game->getRoom());
            $this->getPersistence()->save($nextGame);
            return $this->redirect($this->generateUrl('lichess_game', array('hash' => $nextGame->getHash())));
        }

        $nextPlayer = $this->container->getLichessGeneratorService()->createReturnGame($player);
        $this->getPersistence()->save($nextPlayer->getGame());
        $player->getOpponent()->getStack()->addEvent(array('type' => 'reload_table'));
        $this->getPersistence()->save($game);
        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $nextPlayer->getFullHash())));
    }

    public function syncAction($hash, $version)
    {
        $player = $this->findPlayer($hash);
        $this->getSynchronizer()->setAlive($player);
        $this->getPersistence()->save($player->getGame());
        return $this->renderJson($this->getPlayerSyncData($player, $version));
    }

    protected function getPlayerSyncData($player, $clientVersion)
    {
        $version = $player->getStack()->getVersion();
        $isOpponentConnected = $this->getSynchronizer()->isConnected($player->getOpponent());
        try {
            $events = $version != $clientVersion ? $this->getSynchronizer()->getDiffEvents($player, $clientVersion) : array();
        }
        catch(\OutOfBoundsException $e) {
            $events = array(array('type' => 'redirect', 'url' => $this->generateUrl('lichess_player', array('hash' => $player->getFullHash()))));
        }

        return array('v' => $version, 'o' => $isOpponentConnected, 'e' => $events);
    }

    public function forceResignAction($hash)
    {
        $player = $this->findPlayer($hash);
        if(!$player->getGame()->getIsFinished() && $this->getSynchronizer()->isTimeout($player->getOpponent())) {
            $player->getGame()->setStatus(Game::TIMEOUT);
            $player->setIsWinner(true);
            $player->getStack()->addEvent(array('type' => 'end'));
            $player->getOpponent()->getStack()->addEvent(array('type' => 'end'));
            $this->getPersistence()->save($player->getGame());
        }
        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $hash)));
    }

    protected function renderJson($data)
    {
        $response = $this->createResponse(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    public function moveAction($hash, $version)
    {
        $player = $this->findPlayer($hash);
        $this->getSynchronizer()->setAlive($player);
        if(!$player->isMyTurn()) {
            throw new \LogicException('Not my turn');
        }
        $opponent = $player->getOpponent();
        $game = $player->getGame();
        $move = $this->getRequest()->get('from').' '.$this->getRequest()->get('to');
        $stack = new Stack();
        $manipulator = new Manipulator($game, $stack);
        $opponentPossibleMoves = $manipulator->play($move, $this->getRequest()->get('options', array()));
        $player->getStack()->addEvents($stack->getEvents());
        $player->getStack()->addEvent(array('type' => 'possible_moves', 'possible_moves' => null));
        $response = $this->renderJson($this->getPlayerSyncData($player, $version));
        
        if($opponent->getIsAi()) {
            if(!empty($opponentPossibleMoves)) {
                $stack->reset();
                $possibleMoves = $manipulator->play($this->container->getLichessAiService()->move($game, $opponent->getAiLevel()));
                $player->getStack()->addEvents($stack->getEvents());
                $player->getStack()->addEvent(array('type' => 'possible_moves', 'possible_moves' => $possibleMoves));
            }
        }
        else {
            $opponent->getStack()->addEvents($stack->getEvents());
            $opponent->getStack()->addEvent(array('type' => 'possible_moves', 'possible_moves' => $opponentPossibleMoves));
        }
        $this->getPersistence()->save($game);

        return $response;
    }

    public function showAction($hash)
    {
        $player = $this->findPlayer($hash);
        $game = $player->getGame();

        $this->getSynchronizer()->setAlive($player);

        if(!$game->getIsStarted()) {
            return $this->render('LichessBundle:Player:waitNext', array('player' => $player, 'parameters' => $this->container->getParameterBag()->all()));
        }

        $analyser = new Analyser($game->getBoard());
        $isKingAttacked = $analyser->isKingAttacked($game->getTurnPlayer());
        if($isKingAttacked) {
            $checkSquareKey = $game->getTurnPlayer()->getKing()->getSquareKey();
        }
        else {
            $checkSquareKey = null;
        }
        return $this->render('LichessBundle:Player:show', array(
            'player' => $player,
            'isOpponentConnected' => $this->getSynchronizer()->isConnected($player->getOpponent()),
            'checkSquareKey' => $checkSquareKey,
            'parameters' => $this->container->getParameterBag()->all(),
            'possibleMoves' => ($player->isMyTurn() && !$game->getIsFinished()) ? $analyser->getPlayerPossibleMoves($player, $isKingAttacked) : null
        ));
    }

    /**
     * Add a message to the chat room 
     */
    public function sayAction($hash, $version)
    {
        if('POST' !== $this->getRequest()->getMethod()) {
            throw new NotFoundHttpException('POST method required');
        }
        if(!$message = $this->getRequest()->get('message')) {
            throw new NotFoundHttpException('No message');
        }
        $message = substr($message, 0, 140);
        $player = $this->findPlayer($hash);
        $this->getSynchronizer()->setAlive($player);
        $player->getGame()->getRoom()->addMessage($player->getColor(), $message);
        $sayEvent = array(
            'type' => 'message',
            'html' => sprintf('<li class="%s">%s</li>', $player->getColor(), htmlentities($message, ENT_COMPAT, 'UTF-8'))
        );
        $player->getStack()->addEvent($sayEvent);
        $player->getOpponent()->getStack()->addEvent($sayEvent);
        $this->getPersistence()->save($player->getGame());

        return $this->renderJson($this->getPlayerSyncData($player, $version));
    }

    public function playWithAnybodyAction($hash)
    {
        $connectionFile = $this->container->getParameter('lichess.anybody.connection_file');
        $player = $this->findPlayer($hash);
        $this->getSynchronizer()->setAlive($player);
        if(file_exists($connectionFile)) {
            $opponentHash = file_get_contents($connectionFile);
            if($opponentHash == $hash) {
                return $this->render('LichessBundle:Player:waitAnybody', array('player' => $player, 'parameters' => $this->container->getParameterBag()->all()));
            }
            unlink($connectionFile);
            $opponent = $this->findPlayer($opponentHash);
            if(!$this->getSynchronizer()->isTimeout($opponent)) {
                return $this->redirect($this->generateUrl('lichess_game', array('hash' => $opponent->getGame()->getHash())));
            }
        }

        file_put_contents($connectionFile, $hash);
        return $this->render('LichessBundle:Player:waitAnybody', array('player' => $player, 'parameters' => $this->container->getParameterBag()->all()));
    }

    public function inviteAiAction($hash)
    {
        $player = $this->findPlayer($hash);
        $game = $player->getGame();
        if($game->getIsStarted()) {
            throw new \LogicException('Game already started');
        }

        $opponent = $player->getOpponent();
        $opponent->setIsAi(true);
        $opponent->setAiLevel(1);
        $game->start();

        if($player->isBlack()) {
            $manipulator = new Manipulator($game, new Stack());
            $manipulator->play($this->container->getLichessAiService()->move($game, $opponent->getAiLevel()));
        }
        $this->getPersistence()->save($game);

        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $player->getFullHash())));
    }

    public function resignAction($hash, $version)
    {
        $player = $this->findPlayer($hash);
        $opponent = $player->getOpponent();

        $player->getGame()->setStatus(Game::RESIGN);
        $opponent->setIsWinner(true);
        $player->getStack()->addEvent(array('type' => 'end'));
        $opponent->getStack()->addEvent(array('type' => 'end'));
        $this->getPersistence()->save($player->getGame());
        return $this->renderJson($this->getPlayerSyncData($player, $version));
    }

    public function aiLevelAction($hash)
    {
        $player = $this->findPlayer($hash);
        $level = min(8, max(1, (int)$this->getRequest()->get('level')));
        $player->getOpponent()->setAiLevel($level);
        $this->getPersistence()->save($player->getGame());
        return $this->createResponse('done');
    }

    public function tableAction($hash)
    {
        $player = $this->findPlayer($hash);
        $template = $player->getGame()->getIsFinished() ? 'tableEnd' : 'table';
        return $this->render('LichessBundle:Game:'.$template, array('player' => $player, 'isOpponentConnected' => $this->getSynchronizer()->isConnected($player->getOpponent())));
    }

    public function opponentAction($hash)
    {
        $player = $this->findPlayer($hash);
        return $this->render('LichessBundle:Player:opponentStatus', array('player' => $player, 'isOpponentConnected' => $this->getSynchronizer()->isConnected($player->getOpponent())));
    }

    /**
     * Get the player for this hash 
     * 
     * @param string $hash 
     * @return Player
     */
    protected function findPlayer($hash)
    {
        $gameHash = substr($hash, 0, 6);
        $playerHash = substr($hash, 6, 10);

        $game = $this->getPersistence()->find($gameHash);
        if(!$game) {
            throw new NotFoundHttpException('Can\'t find game '.$gameHash);
        } 

        $player = $game->getPlayerByHash($playerHash);
        if(!$player) {
            throw new NotFoundHttpException('Can\'t find player '.$playerHash);
        } 

        return $player;
    }

    protected function getSynchronizer()
    {
        return $this->container->getLichessSynchronizerService();
    }

    protected function getPersistence()
    {
        return $this->container->getLichessPersistenceService();
    }
}
