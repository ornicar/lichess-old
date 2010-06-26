<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;
use Bundle\LichessBundle\Entities\Game;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;

class GameController extends Controller
{
    /**
     * Join a game and start it
     */
    public function showAction($hash)
    {
        $game = $this->findGame($hash);

        if($game->getIsStarted()) {
            $response = $this->render('LichessBundle:Game:alreadyStarted');
            $response->setStatusCode(410);
            return $response;
        }

        $game->start();
        $game->getCreator()->getStack()->addEvent(array(
            'type' => 'redirect',
            'url' => $this->generateUrl('lichess_player', array('hash' => $game->getCreator()->getFullHash()))
        ));
        $this->container->getLichessPersistenceService()->save($game);
        return $this->redirect($this->generateUrl('lichess_player', array('hash' => $game->getInvited()->getFullHash())));
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
