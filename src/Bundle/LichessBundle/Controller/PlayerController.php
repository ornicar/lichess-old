<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Stack;
use Bundle\LichessBundle\Ai\Crafty;
use Bundle\LichessBundle\Ai\Stupid;
use Bundle\LichessBundle\Entities\Player;
use Bundle\LichessBundle\Entities\Game;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PlayerController extends Controller
{

    public function rematchAction($hash)
    {
        $player = $this->findPlayer($hash);
        $opponent = $player->getOpponent();
        $game = $player->getGame();

        if($nextPlayerHash = $game->getNext()) {
            $nextOpponent = $this->findPlayer($nextPlayerHash);
            if($nextOpponent->getColor() == $player->getColor()) {
                $nextGame = $nextOpponent->getGame();
                $nextGame->setRoom(clone $game->getRoom());
                $this->getPersistence()->save($nextGame);
                $opponent->getStack()->addEvent(array('type' => 'redirect', 'url' => $this->generateUrl('lichess_player', array('hash' => $nextOpponent->getFullHash()))));
                $this->getPersistence()->save($game);
                if($this->getSynchronizer()->isConnected($opponent)) {
                    $this->getSynchronizer()->setAlive($nextOpponent);
                }
                return $this->redirect($this->generateUrl('lichess_game', array('hash' => $nextGame->getHash())));
            }
        }
        else {
            $nextPlayer = $this->container->getLichessGeneratorService()->createReturnGame($player);
            $this->getPersistence()->save($nextPlayer->getGame());
            $opponent->getStack()->addEvent(array('type' => 'reload_table'));
            $this->getSynchronizer()->setAlive($player);
            $this->getPersistence()->save($game);
        }

        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $player->getFullHash())));
    }

    public function syncAction($hash, $color, $version, $playerFullHash)
    {
        $player = $this->findPublicPlayer($hash, $color);
        if($playerFullHash) {
            $this->getSynchronizer()->setAlive($player);
            $this->getPersistence()->save($player->getGame());
        }
        $data = $this->getPlayerSyncData($player, $version);
        // remove private events if user is spectator
        if(!$playerFullHash) {
            foreach($data['e'] as $index => $event) {
                if('message' === $event['type'] || 'redirect' === $event['type']) {
                    unset($data['e'][$index]);
                }
            }
        }
        return $this->renderJson($data);
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
        $postData = $this['request']->request;
        $move = $postData->get('from').' '.$postData->get('to');
        $stack = new Stack();
        $manipulator = new Manipulator($game, $stack);
        $opponentPossibleMoves = $manipulator->play($move, $postData->get('options', array()));
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
            throw new HttpException('Game not started', 410);
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
        if('POST' !== $this['request']->getMethod()) {
            throw new NotFoundHttpException('POST method required');
        }
        $message = trim($this['request']->get('message'));
        if('' === $message) {
            throw new NotFoundHttpException('No message');
        }
        $message = substr($message, 0, 140);
        $player = $this->findPlayer($hash);
        $this->getSynchronizer()->setAlive($player);
        $player->getGame()->getRoom()->addMessage($player->getColor(), $message);
        $htmlMessage = \Bundle\LichessBundle\Helper\TextHelper::autoLink(htmlentities($message, ENT_COMPAT, 'UTF-8'));
        $sayEvent = array(
            'type' => 'message',
            'html' => sprintf('<li class="%s">%s</li>', $player->getColor(), $htmlMessage)
        );
        $player->getStack()->addEvent($sayEvent);
        $player->getOpponent()->getStack()->addEvent($sayEvent);
        $this->getPersistence()->save($player->getGame());

        return $this->renderJson($this->getPlayerSyncData($player, $version));
    }

    public function waitAnybodyAction($hash)
    {
        $player = $this->findPlayer($hash);
        if($player->getGame()->getIsStarted()) {
            return $this->redirect($this->generateUrl('lichess_player', array('hash' => $hash)));
        }
        $this->getSynchronizer()->setAlive($player);

        return $this->render('LichessBundle:Player:waitAnybody', array('player' => $player, 'parameters' => $this->container->getParameterBag()->all()));
    }

    public function waitFriendAction($hash)
    {
        $player = $this->findPlayer($hash);
        if($player->getGame()->getIsStarted()) {
            return $this->redirect($this->generateUrl('lichess_player', array('hash' => $hash)));
        }
        $this->getSynchronizer()->setAlive($player);

        return $this->render('LichessBundle:Player:waitFriend', array('player' => $player, 'parameters' => $this->container->getParameterBag()->all()));
    }

    public function resignAction($hash)
    {
        $player = $this->findPlayer($hash);
        $opponent = $player->getOpponent();

        $player->getGame()->setStatus(Game::RESIGN);
        $opponent->setIsWinner(true);
        $player->getStack()->addEvent(array('type' => 'end'));
        $opponent->getStack()->addEvent(array('type' => 'end'));
        $this->getPersistence()->save($player->getGame());

        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $hash)));
    }

    public function aiLevelAction($hash)
    {
        $player = $this->findPlayer($hash);
        $level = min(8, max(1, (int)$this['request']->get('level')));
        $player->getOpponent()->setAiLevel($level);
        $this->getPersistence()->save($player->getGame());
        return $this->createResponse('done');
    }

    public function tableAction($hash, $color, $playerFullHash)
    {
        if($playerFullHash) {
            $player = $this->findPlayer($playerFullHash);
            $template = $player->getGame()->getIsFinished() ? 'tableEnd' : 'table';
            if($nextPlayerHash = $player->getGame()->getNext()) {
                $nextGame = $this->findPlayer($nextPlayerHash)->getGame();
            }
            else {
                $nextGame = null;
            }
        }
        else {
            $player = $this->findPublicPlayer($hash, $color);
            $template = 'watchTable';
            $nextGame = null;
        }
        return $this->render('LichessBundle:Game:'.$template, array('player' => $player, 'isOpponentConnected' => $this->getSynchronizer()->isConnected($player->getOpponent()), 'nextGame' => $nextGame));
    }

    public function opponentAction($hash, $color, $playerFullHash)
    {
        if($playerFullHash) {
            $player = $this->findPlayer($playerFullHash);
            $template = 'opponent';
        }
        else {
            $player = $this->findPublicPlayer($hash, $color);
            $template = 'watchOpponent';
        }
        return $this->render('LichessBundle:Player:'.$template, array('player' => $player, 'isOpponentConnected' => $this->getSynchronizer()->isConnected($player->getOpponent())));
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

    /**
     * Get the public player for this hash 
     * 
     * @param string $hash 
     * @return Player
     */
    protected function findPublicPlayer($hash, $color)
    {
        $game = $this->getPersistence()->find($hash);
        if(!$game) {
            throw new NotFoundHttpException('Can\'t find game '.$gameHash);
        } 

        $player = $game->getPlayer($color);
        if(!$player) {
            throw new NotFoundHttpException('Can\'t find player '.$color);
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
