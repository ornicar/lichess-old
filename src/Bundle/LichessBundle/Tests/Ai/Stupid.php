<?php

namespace Bundle\LichessBundle\Tests\Ai;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Ai\Stupid;

require_once __DIR__.'/../gameBootstrap.php';
require_once __DIR__.'/../../Ai.php';
require_once __DIR__.'/../../Ai/Stupid.php';

class StupidTest extends \PHPUnit_Framework_TestCase
{

    protected function createPlayer($color = 'white')
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $player = $game->getPlayer($color);

        return $player;
    }

    public function testCreateAi()
    {
        $ai = new Stupid($this->createPlayer());
        $this->assertTrue($ai instanceof Stupid);
    }
}
