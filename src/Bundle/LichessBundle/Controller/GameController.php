<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Entities\Game;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Stack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GameController extends Controller
{
    public function listAction()
    {
        return $this->render('LichessBundle:Game:list.php');
    }

    public function listInnerAction()
    {
        $games = $this['lichess_persistence']->findAll(array(), array('upd' => -1), 9);

        return $this->render('LichessBundle:Game:listInner.php', array('games' => $games));
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

        $game->start();
        $game->getCreator()->getStack()->addEvent(array(
            'type' => 'redirect',
            'url' => $this->generateUrl('lichess_player', array('hash' => $game->getCreator()->getFullHash()))
        ));
        $this->container->getLichessPersistenceService()->save($game);
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
        $player = $this->container->getLichessGeneratorService()->createGameForPlayer($color);
        $this->container->getLichessPersistenceService()->save($player->getGame());
        return $this->redirect($this->generateUrl('lichess_wait_friend', array('hash' => $player->getFullHash())));
    }

    public function inviteAiAction($color)
    {
        $player = $this->container->getLichessGeneratorService()->createGameForPlayer($color);
        $game = $player->getGame();
        $opponent = $player->getOpponent();
        $opponent->setIsAi(true);
        $opponent->setAiLevel(1);
        $game->start();

        if($player->isBlack()) {
            $manipulator = new Manipulator($game, new Stack());
            $manipulator->play($this->container->getLichessAiService()->move($game, $opponent->getAiLevel()));
        }
        $this->container->getLichessPersistenceService()->save($game);

        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $player->getFullHash())));
    }

    public function inviteAnybodyAction($color)
    {
        $connectionFile = $this->container->getParameter('lichess.anybody.connection_file');
        $persistence = $this->container->getLichessPersistenceService();
        $sessionInvites = $this['session']->get('lichess.invites', array());
        if(file_exists($connectionFile)) {
            $opponentHash = file_get_contents($connectionFile);
            // if I'm about to join my own game
            if(in_array($opponentHash, $sessionInvites)) {
                return $this->redirect($this->generateUrl('lichess_wait_anybody', array('hash' => $opponentHash)));
            }
            unlink($connectionFile);
            $gameHash = substr($opponentHash, 0, 6);
            $game = $persistence->find($gameHash);
            if($game) {
                if($this->container->getLichessSynchronizerService()->isConnected($game->getCreator())) {
                    return $this->redirect($this->generateUrl('lichess_game', array('hash' => $game->getHash())));
                }
                // if the game creator is disconnected, remove the game
                $persistence->remove($game);
            }
        }

        $player = $this->container->getLichessGeneratorService()->createGameForPlayer($color);
        $persistence->save($player->getGame());
        file_put_contents($connectionFile, $player->getFullHash());
        $sessionInvites[] = $player->getFullHash();
        $this['session']->set('lichess.invites', $sessionInvites);
        return $this->redirect($this->generateUrl('lichess_wait_anybody', array('hash' => $player->getFullHash())));
    }

    /**
     * Return the game for this hash
     *
     * @param string $hash
     * @return Game
     */
    protected function findGame($hash)
    {
        $game = $this->container->getLichessPersistenceService()->find($hash);

        if(!$game) {
            throw new NotFoundHttpException('Can\'t find game '.$hash);
        }

        return $game;
    }
}
