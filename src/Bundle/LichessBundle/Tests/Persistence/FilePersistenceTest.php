<?php

namespace Bundle\LichessBundle\Tests\Persistence;

use Bundle\LichessBundle\Persistence\FilePersistence;
use Bundle\LichessBundle\Chess\Generator;

require_once __DIR__.'/../../Persistence/PersistenceInterface.php';
require_once __DIR__.'/../../Persistence/FilePersistence.php';
require_once __DIR__.'/../../Chess/Generator.php';

class FilePersistenceTest extends \PHPUnit_Framework_TestCase
{

    public function testCreation()
    {
        $persistence = $this->createPersistence();
        $this->assertEquals('Bundle\LichessBundle\Persistence\FilePersistence', get_class($persistence));
    }

    public function testSave()
    {
        $persistence = $this->createPersistence();
        $game = $this->createGame();

        $persistence->save($game);

        $this->assertTrue(file_exists($this->getDir().'/'.$game->getHash()));

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
     * @return FilePersistence
     */
    protected function createPersistence()
    {
        return new FilePersistence($this->getDir());
    }

    protected function getDir()
    {
        $dir = sys_get_temp_dir().'/lichess';
        if(!is_dir($dir)) {
            mkdir($dir);
        }

        return $dir;
    }

    protected function createGame()
    {
        $generator = new Generator();
        $game = $generator->createGame();

        return $game;
    }

}
