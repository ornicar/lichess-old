<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;
use Bundle\LichessBundle\Entities\Game;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;

class GameController extends Controller
{
    public function showAction($hash)
    {
        $game = $this->findGame($hash);

        if($game->getIsStarted()) {
            $response = $this->render('LichessBundle:Game:alreadyStarted');
            $response->setStatusCode(410);
            return $response;
        }

        $game->setStatus(Game::STARTED);
        $player = $game->getInvited();
        $this->container->getLichessPersistenceService()->save($game);
        $this->container->getLichessSocketService()->write($player->getOpponent(), array(
            'url' => $this->generateUrl('lichess_player', array('hash' => $player->getOpponent()->getFullHash()))
        ));

        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $player->getFullHash())));
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
