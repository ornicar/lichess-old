<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\WebBundle\Controller;
use Bundle\LichessBundle\Socket;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;

class ChatController extends Controller
{
    /**
     * Add a message to the chat room 
     */
    public function sayAction($hash)
    {
        if('POST' !== $this->getRequest()->getMethod()) {
            throw new NotFoundHttpException('POST method required');
        }
        if(!$message = $this->getRequest()->get('message')) {
            throw new NotFoundHttpException('No message');
        }
        $message = substr($message, 0, 140);
        $player = $this->findPlayer($hash);
        $game = $player->getGame();
        $room = $game->getRoom();
        $room->addMessage($player->getColor(), $message);
        $this->container->getLichessPersistenceService()->save($game);

        $socket = new Socket($player->getOpponent(), $this->container['kernel.root_dir'].'/cache/socket');
        $data = array(
            'time' => time(),
            'events' => array(
                array(
                    'type' => 'message',
                    'html' => sprintf('<li><em class="%s"></em>%s</li>', $player->getColor(), htmlentities($message, ENT_COMPAT, 'UTF-8'))
                )
            )
        );
        $socket->write($data);

        $response = $this->createResponse(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
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
