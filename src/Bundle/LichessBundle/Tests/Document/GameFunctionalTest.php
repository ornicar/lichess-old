<?php

namespace Bundle\LichessBundle\Document;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bundle\LichessBundle\Chess\Generator\StandardPositionGenerator;

class GameFunctionalTest extends WebTestCase
{
    protected $dm;
    protected $game;

    public function testInsertGame()
    {
        $this->dm->persist($this->game);
        $this->dm->flush();
    }

    public function setUp()
    {
        $kernel = $this->createKernel();
        $kernel->boot();
        $this->dm = $kernel->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $game = new Game();
        $game->setPlayer(new Player('white'));
        $game->setPlayer(new Player('black'));
        $positionGenerator = new StandardPositionGenerator();
        $positionGenerator->createPieces($game);
        $this->game = $game;
    }

    public function tearDown()
    {
        unset($this->dm, $this->game);
    }
}
