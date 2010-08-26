<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    public function exportAction($hash)
    {
        $game = $this->findGame($hash);
        $dumper = new PgnDumper();
        $pgn = $dumper->dumpGame($game);

        $response = $this->createResponse($pgn);
        $response->headers->set('Content-Type', 'text/plain');
        return $response;
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
