<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Config\ApiConfig;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{
    public function newAction()
    {
        $config = new ApiConfig();
        $game = $this->get('lichess.starter.api')->start($config)->getGame();
        $this->get('doctrine.odm.mongodb.document_manager')->flush();

        $data = array();
        foreach ($game->getPlayers() as $player) {
            $data[$player->getColor()] = $this->get('router')->generate('lichess_player', array('id' => $player->getFullId()), true);
        }

        return new Response(json_encode($data), 200, array('Content-Type' => 'application/json'));
    }
}
