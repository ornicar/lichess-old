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
        $entry = new QueueEntry($config->times, $config->variants, uniqid());
        $result = $queue->add($entry, 'white');
        $this->assertEquals($queue::QUEUED, $result['status']);
        $this->assertNotNull($result['game']);
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
        $entry = new QueueEntry($config->times, $config->variants, uniqid());
        $result = $queue->add($entry, 'white');
        $this->assertEquals($queue::QUEUED, $result['status']);
        $this->assertEquals(1, $queue->count());

        $config2 = new Form\AnybodyGameConfig();
        $entry2 = new QueueEntry($config2->times, $config2->variants, uniqid());
        $result2 = $queue->add($entry2, 'white');
        $this->assertEquals($queue::FOUND, $result2['status']);
        $this->assertEquals($result['game']->getId(), $result2['game_id']);
        $this->assertEquals(0, $queue->count());
    }

    protected function getQueue(Generator $generator)
    {
        return new MongoDBQueue($generator);
    }

    protected function getGeneratorMock()
    {
        return new Generator();
    }
}
