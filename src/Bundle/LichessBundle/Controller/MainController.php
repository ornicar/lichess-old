<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Framework\WebBundle\Controller;
use Bundle\LichessBundle as Lichess;

class MainController extends Controller
{

    public function indexAction()
    {
        $generator = new Lichess\Chess\Generator();
        $player = $generator->createGame()->getPlayer('white');

        return $this->render('LichessBundle:Main:index', array(
            'player' => $player
        ));
    }
}
