<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\WebBundle\Controller;
use Bundle\LichessBundle\Entities\Game;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Socket;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;

class GameController extends Controller
{
    public function showAction($hash)
    {
        $game = $this->findGame($hash);

        if($game->getIsStarted()) {
            return $this->render('LichessBundle:Game:alreadyStarted');
        }

        $player = $game->getInvited();
        $game->setStatus(Game::STARTED);
        $time = time();
        $player->setTime($time);
        $player->getOpponent()->setTime($time);
        $this->container->getLichessPersistenceService()->save($game);
        $socket = new Socket($player->getOpponent(), $this->container['kernel.root_dir'].'/cache/socket');
        $socket->write(array('url' => $this->generateUrl('lichess_player', array( 'hash' => $player->getOpponent()->getFullHash()))));

        return $this->redirect($this->generateUrl('lichess_player', array( 'hash' => $player->getFullHash())));
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
