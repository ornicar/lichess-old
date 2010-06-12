<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\WebBundle\Controller;
use Bundle\LichessBundle\Socket;
use Bundle\LichessBundle\Chess\Generator;

class MainController extends Controller
{

    public function indexAction($color)
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $player = $game->getPlayer($color);
        $game->setCreator($player);

        $this->container->getLichessPersistenceService()->save($game);
        $socket = new Socket($player, $this->container['kernel.root_dir'].'/cache/socket');
        $socket->write(array());

        return $this->render('LichessBundle:Main:index', array(
            'player' => $player
        ));
    }
}
