<?php

namespace Bundle\LichessBundle\Tests\Persistence;

use Bundle\LichessBundle\Persistence\MongoDBQueue;
use Bundle\LichessBundle\Persistence\QueueEntry;
use Bundle\LichessBundle\Form;
use Bundle\LichessBundle\Chess\Generator;

class MongoDBQueueTest extends \PHPUnit_Framework_TestCase
{

    public function testAddQueued()
    {
        $generator = $this->getGeneratorMock();
        $queue = $this->getQueue($generator);
        $queue->clear();
        $config = new Form\AnybodyGameConfig();
        $entry = new QueueEntry($config->times);
        $result = $queue->add($entry, 'white');
        $this->assertEquals($queue::QUEUED, $result['status']);
        $this->assertNotNull($result['game_hash']);
    }

    /**
     * @depends testAddQueued
     */
    public function testCount()
    {
        $generator = $this->getGeneratorMock();
        $queue = $this->getQueue($generator);
        $this->assertEquals(1, $queue->count());
    }

    /**
     * @depends testCount
     */
    public function testClear()
    {
        $generator = $this->getGeneratorMock();
        $queue = $this->getQueue($generator);
        $queue->clear();
        $this->assertEquals(0, $queue->count());
    }

    public function testAddFound()
    {
        $generator = $this->getGeneratorMock();
        $queue = $this->getQueue($generator);
        $queue->clear();
        $config = new Form\AnybodyGameConfig();
        $entry = new QueueEntry($config->times);
        $result = $queue->add($entry, 'white');
        $this->assertEquals($queue::QUEUED, $result['status']);
        $this->assertEquals(1, $queue->count());

        $config2 = new Form\AnybodyGameConfig();
        $entry2 = new QueueEntry($config2->times);
        $result2 = $queue->add($entry2, 'white');
        $this->assertEquals($queue::FOUND, $result2['status']);
        $this->assertEquals($result['game_hash'], $result2['game_hash']);
        $this->assertEquals(0, $queue->count());
    }

    protected function getQueue(Generator $generator)
    {
        return new MongoDBQueue($generator);
    }

    protected function getGeneratorMock()
    {
        $generator = $this->getMock('Bundle\LichessBundle\Chess\Generator');
        $generator->expects($this->any())
            ->method('createGameForPlayer')
            ->will($this->returnValue($this->getGameMock()));

        return $generator;
    }

    protected function getGameMock()
    {
        $game = $this->getMock('Bundle\LichessBundle\Entities\Game');
        $game->expects($this->any())
            ->method('getPlayer')
            ->will($this->returnValue($this->getPlayerMock()));
        $game->expects($this->any())
            ->method('getHash')
            ->will($this->returnValue('abcdef'));

        return $game;
    }

    protected function getPlayerMock()
    {
        $player = $this->getMock('Bundle\LichessBundle\Entities\Player', array(), array('white'));

        return $player;
    }
}
