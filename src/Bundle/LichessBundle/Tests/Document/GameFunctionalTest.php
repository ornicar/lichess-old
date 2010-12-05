<?php

namespace Bundle\LichessBundle\Entity;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bundle\LichessBundle\Chess\Generator\StandardPositionGenerator;

class GameFunctionalTest extends WebTestCase
{
    protected $dm;
    protected $kernel;
    protected $gameClass;
    protected $playerClass;
    protected $clockClass;

    public function setUp()
    {
        $this->kernel = $this->createKernel();
        $this->kernel->boot();

        $this->dm = $this->kernel->getContainer()->get('lichess.object_manager');

        $this->gameClass = $this->kernel->getContainer()->getParameter('lichess.model.game.class');
        $this->playerClass = $this->kernel->getContainer()->getParameter('lichess.model.player.class');
        $this->clockClass = $this->kernel->getContainer()->getParameter('lichess.model.clock.class');

        if ($this->dm instanceof \Doctrine\ORM\EntityManager) {
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->dm);
            $queries = $this->dm->getMetadataFactory()->getAllMetadata();

            try {
                $schemaTool->createSchema($queries);
            }
            catch (\Exception $e) {
                
            }
        }
    }

    public function testCreateGame()
    {
        $game = $this->createGame();
        $this->assertEquals(2, $game->getPlayers()->count());
        $this->assertEquals('white', $game->getPlayer('white')->getColor());
        $this->assertEquals('black', $game->getPlayer('black')->getColor());
        $this->assertFalse($game->getIsStarted());
        $this->assertNull($game->getCreatedAt());
        $this->assertNull($game->getUpdatedAt());
    }

    public function testInsertGame()
    {
        $game = $this->createGame();
        $gameId = $game->getId();
        $this->dm->persist($game);
        $this->dm->flush();
        $this->assertEquals($gameId, $game->getId());
        $this->assertEquals(2, $game->getPlayers()->count());
        $this->assertEquals('white', $game->getPlayer('white')->getColor());
        $this->assertEquals('black', $game->getPlayer('black')->getColor());
        $this->assertInstanceOf('\DateTime', $game->getCreatedAt());

        return $gameId;
    }

    /**
     * @depends testInsertGame
     */
    public function testFetchInsertedGame($gameId)
    {
        $game = $this->dm->getRepository('LichessBundle:Game')->findOneById($gameId);
        $this->assertEquals($gameId, $game->getId());
        $this->assertEquals(2, $game->getPlayers()->count());
        $this->assertEquals('white', $game->getPlayer('white')->getColor());
        $this->assertEquals('black', $game->getPlayer('black')->getColor());
        $this->assertInstanceOf('\DateTime', $game->getCreatedAt());
        $this->assertNull($game->getClock());
        $this->assertNull($game->getRoom());

        return $gameId;
    }

    /**
     * @depends testFetchInsertedGame
     */
    public function testUpdateGame($gameId)
    {
        $game = $this->dm->getRepository('LichessBundle:Game')->findOneById($gameId);
        $game->setVariant(Game::VARIANT_960);
        $game->start();
        $this->dm->flush();
        $this->assertInstanceOf('\DateTime', $game->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $game->getUpdatedAt());

        return $gameId;
    }

    /**
     * @depends testUpdateGame
     */
    public function testFetchUpdatedGame($gameId)
    {
        $game = $this->dm->getRepository('LichessBundle:Game')->findOneById($gameId);
        $this->assertTrue($game->getIsStarted());
        $this->assertEquals(Game::VARIANT_960, $game->getVariant());
        $this->assertEquals(2, $game->getRoom()->getNbMessages());
    }

    public function testInsertFullFeaturedGame()
    {
        $game = $this->createGame();
        $game->setClock(new $this->clockClass(120));
        $game->start();
        $this->dm->persist($game);
        $this->dm->flush();
        $game->getRoom()->addMessage('white', 'Rock\' n roll');
        $game->getRoom()->addMessage('black', 'Ain\'t noise pollution');
        $game->getPlayer('white')->addEventToStack(array('type' => 'test white'));
        $game->getPlayer('black')->addEventToStack(array('type' => 'test black'));
        $this->dm->flush();

        return $game->getId();
    }

    /**
     * @depends testInsertFullFeaturedGame
     */
    public function testFetchFullFeaturedGame($gameId)
    {
        $game = $this->dm->getRepository('LichessBundle:Game')->findOneById($gameId);
        $this->assertInstanceOf('\Bundle\LichessBundle\Model\Clock', $game->getClock());
        $this->assertEquals(2, $game->getClock()->getLimitInMinutes());
        $this->assertEquals(4, $game->getRoom()->getNbMessages());
    }

    public function testAddEventToPlayerStack()
    {
        $game = $this->createGame();
        $player = $game->getPlayer('white');
        $this->assertEquals(1, $player->getStack()->getNbEvents());
        $this->dm->persist($game);
        $this->dm->flush();
        $this->dm->clear();
        $game = $this->dm->getRepository('LichessBundle:Game')->findOneById($game->getId());
        $player = $game->getPlayer('white');
        $this->assertEquals(1, $player->getStack()->getNbEvents());
        $player->addEventsToStack(array(array('type' => 'test'), array('type' => 'test')));
        $this->dm->flush();
        $this->dm->clear();
        $game = $this->dm->getRepository('LichessBundle:Game')->findOneById($game->getId());
        $player = $game->getPlayer('white');
        $this->assertEquals(3, $player->getStack()->getNbEvents());
        for($i=0; $i<$player->getStack()->getMaxEvents(); $i++) {
            $player->addEventToStack(array('type' => 'event '.$i));
        }
        $this->assertEquals($player->getStack()->getMaxEvents(), $player->getStack()->getNbEvents());
        $this->dm->flush();
        $this->dm->clear();
        $game = $this->dm->getRepository('LichessBundle:Game')->findOneById($game->getId());
        $player = $game->getPlayer('white');
        $this->assertEquals($player->getStack()->getMaxEvents(), $player->getStack()->getNbEvents());
    }

    protected function createGame()
    {
        $game = new $this->gameClass();
        $game->addPlayer(new $this->playerClass('white'));
        $game->addPlayer(new $this->playerClass('black'));
        $game->setCreatorColor('white');
        $this->createPiecesMinimal($game);
        return $game;
    }

    public function createPiecesMinimal(Game $game)
    {
        $positionGenerator = $this->kernel->getContainer()->get('lichess_generator');
        $generator = $positionGenerator->getVariantGenerator();

        $pieces = array();
        $player = $game->getPlayer('white');
        $pieces[] = $generator->createPiece('Pawn', 1, 2);
        $player->setPieces($pieces);
        $player->getOpponent()->setPieces($generator->mirrorPieces($pieces));

        $game->setInitialFen(null);
    }
}
