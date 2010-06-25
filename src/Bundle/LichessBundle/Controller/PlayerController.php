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

    public function playAgainAction($hash)
    {
        $player = $this->findPlayer($hash);
        $game = $player->getGame();
        $persistence = $this->container->getLichessPersistenceService();

        if($nextHash = $game->getNext()) {
            $nextGame = $persistence->find($nextHash);
            $nextGame->setRoom(clone $game->getRoom());
            $persistence->save($nextGame);
            return $this->redirect($this->generateUrl('lichess_game', array('hash' => $nextGame->getHash())));
        }

        $nextPlayer = $this->container->getLichessGeneratorService()->createReturnGame($player);
        $persistence->save($nextPlayer->getGame());
        $persistence->save($game);
        $this->container->getLichessSocketService()->write($player->getOpponent(), array('events' => array(array(
            'type' => 'reload_table',
        ))));
        $this->container->getLichessSocketService()->write($nextPlayer, array());
        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $nextPlayer->getFullHash())));
    }

    public function syncAction($hash)
    {
        $player = $this->findPlayer($hash);
        if($player->getOpponent()->getIsAi()) {
            throw new \LogicException('Do not sync with AI');
        }
        $game = $player->getGame();
        $synchronizer = $this->container->getLichessSynchronizerService();
        $synchronizer->update($player);
        $this->container->getLichessPersistenceService()->save($game);
        if(!$game->getIsStarted()) {
            return $this->createResponse('');
        }
        return $this->render('LichessBundle:Player:opponentStatus', array('player' => $player, 'isOpponentConnected' => $synchronizer->isConnected($player->getOpponent())));
    }

    public function forceResignAction($hash)
    {
        $player = $this->findPlayer($hash);
        if(!$player->getGame()->getIsFinished() && $this->container->getLichessSynchronizerService()->isTimeout($player->getOpponent())) {
            $player->getGame()->setStatus(Game::TIMEOUT);
            $player->setIsWinner(true);
            $this->container->getLichessPersistenceService()->save($player->getGame());
            $this->container->getLichessSocketService()->write($player->getOpponent(), array('events' => array(array(
                'type' => 'end',
            ))));
        }
        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $hash)));
    }

    protected function renderJson($data)
    {
        $response = $this->createResponse(empty($data) ? '' : json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    protected function getEndGameData(Player $player)
    {
        return array(
            'time' => time(),
            'possible_moves' => null,
            'events' => array(array(
                'type' => 'end',
            ))
        );
    }
    
    public function moveAction($hash)
    {
        $player = $this->findPlayer($hash);
        if(!$player->isMyTurn()) {
            throw new \LogicException('Not my turn');
        }
        $opponent = $player->getOpponent();
        $game = $player->getGame();
        if(!$opponent->getIsAi()) {
            $this->container->getLichessSynchronizerService()->update($player);
        }
        $move = $this->getRequest()->get('from').' '.$this->getRequest()->get('to');
        $stack = new Stack();
        $manipulator = new Manipulator($game, $stack);
        $opponentPossibleMoves = $manipulator->play($move, $this->getRequest()->get('options', array()));
        $data = array(
            'time' => time(),
            'possible_moves' => null,
            'events' => $stack->getEvents()
        );
        if($game->getIsFinished()) {
            $data['events'][] = array(
                'type' => 'end',
            );
        }
        $response = $this->renderJson($data);
        
        if($opponent->getIsAi()) {
            if(!empty($opponentPossibleMoves)) {
                $ai = $this->container->getLichessAiService();
                $stack->reset();
                $possibleMoves = $manipulator->play($ai->move($game, $opponent->getAiLevel()));
                $data = array(
                    'possible_moves' => $possibleMoves,
                    'events' => $stack->getEvents()
                );
                if($game->getIsFinished()) {
                    $data['events'][] = array(
                        'type' => 'end',
                    );
                }
                $this->container->getLichessSocketService()->write($player, $data);
            }
        }
        else {
            $data = array(
                'possible_moves' => $opponentPossibleMoves,
                'events' => $stack->getEvents()
            );
            if($game->getIsFinished()) {
                $data['events'][] = array(
                    'type' => 'end',
                );
            }
            $this->container->getLichessSocketService()->write($opponent, $data);
        }
        $this->container->getLichessPersistenceService()->save($game);

        return $response;
    }

    public function showAction($hash)
    {
        $player = $this->findPlayer($hash);
        $game = $player->getGame();

        if(!$player->getOpponent()->getIsAi()) {
            $this->container->getLichessSynchronizerService()->update($player);
            $this->container->getLichessPersistenceService()->save($game);
        }

        if(!$game->getIsStarted()) {
            return $this->render('LichessBundle:Player:waitNext', array('player' => $player));
        }

        $analyser = new Analyser($game->getBoard());
        $isKingAttacked = $analyser->isKingAttacked($game->getTurnPlayer());
        if($isKingAttacked) {
            $checkSquareKey = $game->getTurnPlayer()->getKing()->getSquareKey();
        }
        else {
            $checkSquareKey = null;
        }
        $this->container->getLichessSocketService()->write($player, array());
        return $this->render('LichessBundle:Player:show', array(
            'player' => $player,
            'isOpponentConnected' => $this->container->getLichessSynchronizerService()->isConnected($player->getOpponent()),
            'checkSquareKey' => $checkSquareKey,
            'parameters' => $this->container->getParameters(),
            'possibleMoves' => ($player->isMyTurn() && !$game->getIsFinished()) ? $analyser->getPlayerPossibleMoves($player, $isKingAttacked) : null
        ));
    }

    public function playWithAnybodyAction($hash)
    {
        $connectionFile = $this->container->getParameter('lichess.anybody.connection_file');
        $player = $this->findPlayer($hash);
        $this->container->getLichessSynchronizerService()->update($player);
        $this->container->getLichessPersistenceService()->save($player->getGame());
        if(file_exists($connectionFile)) {
            $opponentHash = file_get_contents($connectionFile);
            if($opponentHash == $hash) {
                return $this->render('LichessBundle:Player:waitAnybody', array('player' => $player));
            }
            unlink($connectionFile);
            $opponent = $this->findPlayer($opponentHash);
            if(!$this->container->getLichessSynchronizerService()->isTimeout($opponent)) {
                return $this->redirect($this->generateUrl('lichess_game', array('hash' => $opponent->getGame()->getHash())));
            }
        }

        file_put_contents($connectionFile, $hash);
        return $this->render('LichessBundle:Player:waitAnybody', array('player' => $player));
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
            $ai = $this->container->getLichessAiService();
            $manipulator = new Manipulator($game);
            $manipulator->play($ai->move($game, $opponent->getAiLevel()));
        }
        $this->container->getLichessPersistenceService()->save($game);

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
            $this->container->getLichessSocketService()->write($opponent, $this->getEndGameData($opponent));
        }
        return $this->renderJson($this->getEndGameData($player));
    }

    public function aiLevelAction($hash)
    {
        $player = $this->findPlayer($hash);
        $level = min(8, max(1, (int)$this->getRequest()->get('level')));
        $player->getOpponent()->setAiLevel($level);
        $this->container->getLichessPersistenceService()->save($player->getGame());
        return $this->createResponse('done');
    }

    public function tableAction($hash)
    {
        $player = $this->findPlayer($hash);
        $template = $player->getGame()->getIsFinished() ? 'tableEnd' : 'table';
        return $this->render('LichessBundle:Game:'.$template, array('player' => $player, 'isOpponentConnected' => $this->container->getLichessSynchronizerService()->isConnected($player->getOpponent())));
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
