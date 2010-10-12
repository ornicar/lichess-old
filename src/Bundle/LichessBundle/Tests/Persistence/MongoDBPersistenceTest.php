<?php

namespace Bundle\LichessBundle\Tests\Persistence;

use Bundle\LichessBundle\Persistence\MongoDBPersistence;
use Bundle\LichessBundle\Chess\Generator;

class MongoDBPersistenceTest extends \PHPUnit_Framework_TestCase
{

    public function testMongoBin()
    {
        $data = "string data";
        $bin = new \MongoBinData($data);
        $this->assertEquals($data, $bin->bin);
    }

    public function testMongoBinWithCompressedData()
    {
        $data = "string data";
        $compressed = gzcompress($data, 9);
        $bin = new \MongoBinData($compressed);
        $this->assertEquals($data, gzuncompress($bin->bin));
    }

    public function testMongoBinWithSerializedAndCompressedData()
    {
        $data = "string data";
        $serialized = serialize($data);
        $compressed = gzcompress($serialized, 9);
        $bin = new \MongoBinData($compressed);
        $this->assertEquals($data, unserialize(gzuncompress($bin->bin)));
    }

    public function testCompression()
    {
        $this->compression('string data');
        $game = $this->createGame();
        $this->compression($game);
    }

    protected function compression($data)
    {
        $serialized = serialize($data);
        $compressed = gzcompress($serialized, 9);
        $bin = new \MongoBinData($compressed);
        $this->assertEquals($compressed, $bin->bin);
        $this->assertEquals($serialized, gzuncompress($bin->bin));
    }

    public function testCreation()
    {
        $persistence = $this->createPersistence();
        $this->assertEquals('Bundle\LichessBundle\Persistence\MongoDBPersistence', get_class($persistence));
    }

    public function testSave()
    {
        $persistence = $this->createPersistence();
        $game = $this->createGame();

        $persistence->save($game);

        $this->assertTrue(true);

        return $game;
    }

    /**
     * @depends testSave
     */
    public function testFind($game)
    {
        $persistence = $this->createPersistence();
        $loadedGame= $persistence->find($game->getHash());

        $this->assertEquals($loadedGame->getHash(), $game->getHash());
    }

    /**
     * @return MongoDBPersistence
     */
    protected function createPersistence()
    {
        return new MongoDBPersistence();
    }

    protected function createGame()
    {
        $generator = new Generator();
        $game = $generator->createGame();

        return $game;
    }

}
