<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;
use Bundle\LichessBundle\Notation\PgnDumper;

class PgnController extends Controller
{
    public function analyseAction($hash, $color)
    {
        $game = $this->findGame($hash);
        $dumper = new PgnDumper();
        $pgn = $dumper->dumpGame($game);

        return $this->render('LichessBundle:Pgn:analyse', array(
            'game' => $game,
            'color' => $color,
            'pgn' => $pgn
        ));
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
