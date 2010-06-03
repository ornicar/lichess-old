<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Entities as Entities;

require_once __DIR__.'/../../Entities/Game.php';
require_once __DIR__.'/../../Entities/Player.php';
require_once __DIR__.'/../../Entities/Piece.php';
require_once __DIR__.'/../../Entities/Piece/Pawn.php';
require_once __DIR__.'/../../Entities/Piece/Rook.php';
require_once __DIR__.'/../../Entities/Piece/Knight.php';
require_once __DIR__.'/../../Entities/Piece/Bishop.php';
require_once __DIR__.'/../../Entities/Piece/Queen.php';
require_once __DIR__.'/../../Entities/Piece/King.php';
require_once __DIR__.'/../../Chess/Generator.php';

class GeneratorTest extends \PHPUnit_Framework_TestCase
{

    public function testCreation()
    {
        $generator = new Generator();
        $this->assertEquals('Bundle\LichessBundle\Chess\Generator', get_class($generator));
    }

    public function testGameCreation()
    {
        $generator = new Generator();

        $game = $generator->createGame();

        $this->assertTrue($game instanceof Entities\Game);

        $this->assertEquals(2, count($game->getPlayers()));

        $this->assertEquals(32, count($game->getPieces()));
    }

}
