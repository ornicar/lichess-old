<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\WebBundle\Controller;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Synchronizer;
use Bundle\LichessBundle\Socket;
use Bundle\LichessBundle\Stack;
use Bundle\LichessBundle\Ai\Crafty;
use Bundle\LichessBundle\Ai\Stupid;
use Bundle\LichessBundle\Entities\Player;
use Bundle\LichessBundle\Entities\Game;
use Bundle\LichessBundle\Chess\Generator;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;

class PlayerController extends Controller
{

    public function playAgainAction($hash)
    {
        $player = $this->findPlayer($hash);
        $game = $player->getGame();

        if($nextHash = $game->getNext()) {
            $nextGame = $this->container->getLichessPersistenceService()->find($nextHash);
            $nextGame->setRoom(clone $game->getRoom());
            $this->container->getLichessPersistenceService()->save($nextGame);
            return $this->redirect($this->generateUrl('lichess_game', array('hash' => $nextGame->getHash())));
        }

        $generator = new Generator();
        $nextGame = $generator->createGame();
        $nextPlayer = $nextGame->getPlayer($player->getOpponent()->getColor());
        $nextGame->setCreator($nextPlayer);
        $nextGame->setRoom(clone $game->getRoom());
        $this->container->getLichessPersistenceService()->save($nextGame);
        $game->setNext($nextGame->getHash());
        $this->container->getLichessPersistenceService()->save($game);
        $socket = new Socket($player->getOpponent(), $this->container['kernel.root_dir'].'/cache/socket');
        $socket->write(array('events' => array(array(
            'type' => 'reload_table',
            'table_url' => $this->generateUrl('lichess_table', array('hash' => $player->getOpponent()->getFullHash()))
        ))));
        $socket = new Socket($nextPlayer, $this->container['kernel.root_dir'].'/cache/socket');
        $socket->write(array());
        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $nextPlayer->getFullHash())));
    }

    public function syncAction($hash)
    {
        $player = $this->findPlayer($hash);
        if($player->getOpponent()->getIsAi()) {
            throw new \LogicException('Do not sync with AI');
        }
        $game = $player->getGame();
        if($game->getIsFinished()) {
            $response = $this->createResponse(null);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $synchronizer = new Synchronizer();
        $synchronizer->synchronize($player);
        $this->container->getLichessPersistenceService()->save($game);

        if($game->getIsFinished()) {
            $response = $this->createResponse(json_encode(array(
                'time' => time(),
                'possible_moves' => null,
                'events' => array(array(
                    'type' => 'end',
                    'table_url'  => $this->generateUrl('lichess_table', array('hash' => $player->getFullHash()))
                ))
            )));
            $socket = new Socket($player->getOpponent(), $this->container['kernel.root_dir'].'/cache/socket');
            $socket->write(array(
                'time' => time(),
                'possible_moves' => null,
                'events' => array(array(
                    'type' => 'end',
                    'table_url'  => $this->generateUrl('lichess_table', array('hash' => $player->getOpponent()->getFullHash()))
                ))
            ));
        }
        else {
            $response = $this->createResponse(null);
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    public function moveAction($hash)
    {
        $player = $this->findPlayer($hash);
        $opponent = $player->getOpponent();
        $game = $player->getGame();
        if(!$player->isMyTurn()) {
            throw new \LogicException('Not my turn');
        }
        $move = $this->getRequest()->get('from').' '.$this->getRequest()->get('to');
        $stack = new Stack();
        $manipulator = new Manipulator($game->getBoard(), $stack);
        $opponentPossibleMoves = $manipulator->play($move, $this->getRequest()->get('options', array()));
        $data = array(
            'time' => time(),
            'possible_moves' => null,
            'events' => $stack->getEvents()
        );
        if($game->getIsFinished()) {
            $data['events'][] = array(
                'type' => 'end',
                'table_url'  => $this->generateUrl('lichess_table', array('hash' => $player->getFullHash()))
            );
        }
        $response = $this->createResponse(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        
        if($opponent->getIsAi()) {
            if(empty($opponentPossibleMoves)) {
                $this->container->getLichessPersistenceService()->save($game);
            }
            else {
                $ai = $this->container->getLichessAiService();
                $stack->reset();
                $possibleMoves = $manipulator->play($ai->move($game, $opponent->getAiLevel()));
                $this->container->getLichessPersistenceService()->save($game);

                $socket = new Socket($player, $this->container['kernel.root_dir'].'/cache/socket');
                $data = array(
                    'possible_moves' => $possibleMoves,
                    'events' => $stack->getEvents()
                );
                if($game->getIsFinished()) {
                    $data['events'][] = array(
                        'type' => 'end',
                        'table_url'  => $this->generateUrl('lichess_table', array('hash' => $player->getFullHash()))
                    );
                }
                $socket->write($data);
            }
        }
        else {
            $this->container->getLichessPersistenceService()->save($game);
            $data = array(
                'time' => time(),
                'possible_moves' => $opponentPossibleMoves,
                'events' => $stack->getEvents()
            );
            if($game->getIsFinished()) {
                $data['events'][] = array(
                    'type' => 'end',
                    'table_url'  => $this->generateUrl('lichess_table', array('hash' => $opponent->getFullHash()))
                );
            }
            $socket = new Socket($opponent, $this->container['kernel.root_dir'].'/cache/socket');
            $socket->write($data);
        }

        return $response;
    }

    public function showAction($hash)
    {
        $player = $this->findPlayer($hash);
        $game = $player->getGame();

        if(!$game->getIsStarted()) {
            return $this->render('LichessBundle:Player:waitNext', array(
                'player' => $player
            ));
        }

        if(!$player->getOpponent()->getIsAi() && !$game->getIsFinished()) {
            $synchronizer = new Synchronizer();
            $synchronizer->update($player);
            $this->container->getLichessPersistenceService()->save($game);
        }

        $analyser = new Analyser($game->getBoard());
        $isKingAttacked = $analyser->isKingAttacked($game->getTurnPlayer());
        if($isKingAttacked) {
            $checkSquareKey = $game->getTurnPlayer()->getKing()->getSquareKey();
        }
        else {
            $checkSquareKey = null;
        }
        $socket = new Socket($player, $this->container['kernel.root_dir'].'/cache/socket');
        $socket->write(array());
        return $this->render('LichessBundle:Player:show', array(
            'player' => $player,
            'checkSquareKey' => $checkSquareKey,
            'possibleMoves' => ($player->isMyTurn() && !$game->getIsFinished()) ? $analyser->getPlayerPossibleMoves($player, $isKingAttacked) : null
        ));
    }

    public function playWithAnybodyAction($hash)
    {
        $connectionFile = $this->container['kernel.root_dir'].'/cache/connect_anybody';
        $player = $this->findPlayer($hash);
        if(file_exists($connectionFile)) {
            $opponentHash = file_get_contents($connectionFile);
            if($opponentHash === $hash) {
                return $this->render('LichessBundle:Player:waitAnybody', array('player' => $player));
            }
            unlink($connectionFile);
            $game = $this->findPlayer($opponentHash)->getGame();
            return $this->redirect($this->generateUrl('lichess_game', array('hash' => $game->getHash())));
        }
        else {
            file_put_contents($connectionFile, $hash);
            return $this->render('LichessBundle:Player:waitAnybody', array('player' => $player));
        }
    }

    public function inviteAiAction($hash)
    {
        $player = $this->findPlayer($hash);
        $opponent = $player->getOpponent();
        $game = $player->getGame();

        if($game->getIsStarted()) {
            throw new NotFoundHttpException('Game already started');
        }

        $opponent->setIsAi(true);
        $opponent->setAiLevel(1);
        $game->setStatus(Game::STARTED);

        if($player->isBlack()) {
            $ai = $this->container->getLichessAiService();
            $stack = new Stack();
            $manipulator = new Manipulator($game->getBoard(), $stack);
            $manipulator->play($ai->move($game, $opponent->getAiLevel()));
            $this->container->getLichessPersistenceService()->save($game);
        }
        else {
            $this->container->getLichessPersistenceService()->save($game);
        }

        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $player->getFullHash())));
    }

    public function resignAction($hash)
    {
        $player = $this->findPlayer($hash);
        $game = $player->getGame();
        $opponent = $player->getOpponent();

        $game->setStatus(Game::RESIGN);
        $opponent->setIsWinner(true);
        $this->container->getLichessPersistenceService()->save($game);
        
        if(!$opponent->getIsAi()) {
            $socket = new Socket($opponent, $this->container['kernel.root_dir'].'/cache/socket');
            $socket->write(array(
                'time' => time(),
                'possible_moves' => null,
                'events' => array(array(
                    'type' => 'end',
                    'table_url'  => $this->generateUrl('lichess_table', array('hash' => $opponent->getFullHash()))
                ))
            ));
        }

        $response = $this->createResponse(json_encode(array(
            'time' => time(),
            'possible_moves' => null,
            'events' => array(array(
                'type' => 'end',
                'table_url'  => $this->generateUrl('lichess_table', array('hash' => $player->getFullHash()))
            ))
        )));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function aiLevelAction($hash)
    {
        $player = $this->findPlayer($hash);
        $opponent = $player->getOpponent();
        $level = min(8, max(1, (int)$this->getRequest()->get('level')));
        $opponent->setAiLevel($level);
        $this->container->getLichessPersistenceService()->save($player->getGame());
        return $this->createResponse('done');
    }

    public function tableAction($hash)
    {
        $player = $this->findPlayer($hash);
        $template = $player->getGame()->getIsFinished() ? 'tableEnd' : 'table';
        return $this->render('LichessBundle:Game:'.$template, array( 'player' => $player));
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
