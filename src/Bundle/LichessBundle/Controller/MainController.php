<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\WebBundle\Controller;
use Bundle\LichessBundle as Lichess;

class MainController extends Controller
{

    public function indexAction($color)
    {
        $generator = new Lichess\Chess\Generator();
        $game = $generator->createGame();
        $player = $game->getPlayer($color);
        $game->setCreator($player);

        $this->container->getLichessPersistenceService()->save($game);

        return $this->render('LichessBundle:Main:index', array(
            'player' => $player
        ));
    }
}
