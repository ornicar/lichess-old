<?php

namespace Bundle\LichessBundle\Benchmark;

use Symfony\Framework\FoundationBundle\Test\WebTestCase;
use Bundle\LichessBundle\Entities\Game;

abstract class Benchmark extends WebTestCase
{
    protected $client;
    protected $container;

    protected function setup()
    {
        $this->client = $this->createClient();
        $this->container = $this->client->getKernel()->getContainer();
    }

    protected function createPlayer()
    {
        $player = $this->container->getLichessGeneratorService()->createGameForPlayer('white');
        $player->getGame()->setStatus(Game::STARTED);
        for($it=0; $it<50; $it++) {
            $player->getGame()->getRoom()->addMessage($it%2 ? 'white' : 'black', str_repeat('blah blah '.$it.' ', rand(1, 10)));
        }
        $this->container->getLichessPersistenceService()->save($player->getGame());
        return $player;
    }
}
