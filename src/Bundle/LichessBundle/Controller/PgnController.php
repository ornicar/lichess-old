<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

class PgnController extends Controller
{
    public function analyseAction($id, $color)
    {
        $game = $this->findGame($id);
        $player = $game->getPlayer($color);

        $data = $this->get('lila')->gameInfo($game);

        return $this->render('LichessBundle:Pgn:analyse.html.twig', array(
            'game'         => $game,
            'player'       => $player,
            'reverseColor' => 'white' === $color ? 'black' : 'white',
            'pgn'          => $data['pgn'],
            'opening' => $data['opening']
        ));
    }

    /**
     * Return the game for this id
     *
     * @param string $id
     * @return Game
     */
    protected function findGame($id)
    {
        $game = $this->get('lichess.repository.game')->findOneById($id);

        if(!$game) {
            throw new NotFoundHttpException('Can\'t find game '.$id);
        }

        return $game;
    }
}
