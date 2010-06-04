<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\WebBundle\Controller;
use Bundle\LichessBundle as Lichess;

class MainController extends Controller
{

    public function indexAction($color)
    {
        $generator = new Lichess\Chess\Generator();
        $player = $generator->createGame()->getPlayer($color);

        return $this->render('LichessBundle:Main:index', array(
            'player' => $player
        ));
    }
}
