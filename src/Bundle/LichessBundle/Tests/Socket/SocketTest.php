<?php

namespace Bundle\LichessBundle\Tests\Socket;

use Bundle\LichessBundle\Socket;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Analyser;

require_once __DIR__.'/../gameBootstrap.php';
require_once __DIR__.'/../../Socket.php';

class SocketTest extends \PHPUnit_Framework_TestCase
{
    protected $socket;
    protected $game;
    protected $board;

    public function testConstruct()
    {
        $socket = $this->create();
        $this->assertTrue($socket instanceof Socket);
        $this->assertTrue(is_dir($socket->getDir()));
        $this->assertTrue(is_writable($socket->getDir()));
        $this->assertTrue(!file_exists($socket->getFile()));
    }

    protected function create()
    {
        $generator = new Generator();
        $this->game = $generator->createGame();
        $this->board = $this->game->getBoard();
        $dir = sys_get_temp_dir();
        return $this->socket = new Socket($this->game->getTurnPlayer(), $dir);
    }
}
