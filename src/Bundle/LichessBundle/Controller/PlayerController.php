<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\WebBundle\Controller;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Socket;
use Bundle\LichessBundle\Stack;
use Bundle\LichessBundle\Ai\Crafty;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;

class PlayerController extends Controller
{
    public function moveAction($hash)
    {
        $player = $this->findPlayer($hash);
        $opponent = $player->getOpponent();
        $game = $player->getGame();
        if(!$player->isMyTurn()) {
            throw new NotFoundHttpException('Not my turn');
        }
        $move = $this->getRequest()->get('from').' '.$this->getRequest()->get('to');
        $stack = new Stack();
        $manipulator = new Manipulator($game->getBoard(), $stack);
        try {
            $opponentPossibleMoves = $manipulator->play($move);
        }
        catch(Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
        if($opponent->getIsAi()) {
            $ai = new Crafty($opponent);
            $stack->reset();
            $possibleMoves = $manipulator->play($ai->move());
            $this->container->getLichessPersistenceService()->save($game);

            $socket = new Socket($player, $this->container['kernel.root_dir'].'/cache/socket');
            $socket->write(array(
                'status' => Socket::UPDATE,
                'possible_moves' => $possibleMoves,
                'finished' => $game->getIsFinished(),
                'events' => $stack->getEvents()
            ));
        }
        else {
            $this->container->getLichessPersistenceService()->save($game);
            $socket = new Socket($player->getOpponent(), $this->container['kernel.root_dir'].'/cache/socket');
            $socket->write(array(
                'status' => Socket::UPDATE,
                'possible_moves' => $opponentPossibleMoves,
                'finished' => $game->getIsFinished(),
                'events' => $stack->getEvents()
            ));
        }

        return $this->createResponse(json_encode(array(
            'time' => time(),
            'finished' => $game->getIsFinished(),
            'events' => $stack->getEvents()
        )));
    }

    public function showAction($hash)
    {
        $player = $this->findPlayer($hash);

        $analyser = new Analyser($player->getGame()->getBoard());
        $isKingAttacked = $analyser->isKingAttacked($player->getGame()->getTurnPlayer());
        if($isKingAttacked) {
            $checkSquareKey = $game->getTurnPlayer()->getKing()->getSquareKey();
        }
        else {
            $checkSquareKey = null;
        }
        $socket = new Socket($player, $this->container['kernel.root_dir'].'/cache/socket');
        $socket->write(array(
            'status' => 'play'
        ));

        return $this->render('LichessBundle:Player:show', array(
            'player' => $player,
            'checkSquareKey' => $checkSquareKey,
            'possibleMoves' => $player->isMyTurn() ? $analyser->getPlayerPossibleMoves($player, $isKingAttacked) : null
        ));
    }

    public function inviteAiAction($hash)
    {
        $player = $this->findPlayer($hash);
        $game = $player->getGame();

        if($game->getIsStarted()) {
            throw new NotFoundHttpException('Game already started');
        }

        $player->getOpponent()->setIsAi(true);
        $game->setIsStarted(true);
        $this->container->getLichessPersistenceService()->save($game);

        return $this->redirect($this->generateUrl('lichess_player', array(
            'hash' => $player->getFullHash(),
            'checkSquareKey' => null
        )));
    }

    protected function findPlayer($hash)
    {
        $gameHash = substr($hash, 0, 6);
        $playerHash = substr($hash, 6, 10);

        $game = $this->container->getLichessPersistenceService()->find($gameHash);
        if(!$game) {
            throw new NotFoundHttpException('Can\'t find game '.$gameHash);
        } 

        $player = $game->getPlayerByHash($playerHash);
        if(!$player) {
            throw new NotFoundHttpException('Can\'t find player '.$playerHash);
        } 

        return $player;
    }
}
