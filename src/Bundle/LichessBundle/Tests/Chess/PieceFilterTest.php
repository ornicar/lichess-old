<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\PieceFilter;
use Bundle\LichessBundle\Entities as Entities;
use Bundle\LichessBundle\Entities\Piece as Piece;

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
require_once __DIR__.'/../../Chess/Board.php';
require_once __DIR__.'/../../Chess/Square.php';
require_once __DIR__.'/../../Chess/PieceFilter.php';

class PieceFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testGameCreation()
    {
        $generator = new Generator();
        $game = $generator->createGame();

        $this->assertTrue($game instanceof Entities\Game);

        return $game;
    }

    /**
     * @depends testGameCreation
     */
    public function testFilterAlive(Entities\Game $game)
    {
        $piece1 = $game->getBoard()->getPieceByPos(1, 1);
        $piece2 = $game->getBoard()->getPieceByPos(2, 1);
        $piece2->setIsDead(true);

        $this->assertSame(array($piece1), PieceFilter::filterAlive(array($piece1, $piece2)));
    }

    /**
     * @depends testGameCreation
     */
    public function testFilterDead(Entities\Game $game)
    {
        $piece1 = $game->getBoard()->getPieceByPos(1, 1);
        $piece2 = $game->getBoard()->getPieceByPos(2, 1);
        $piece2->setIsDead(true);

        $this->assertSame(array($piece2), PieceFilter::filterDead(array($piece1, $piece2)));
    }

    /**
     * @depends testGameCreation
     */
    public function testFilterProjection(Entities\Game $game)
    {
        $piece1 = $game->getBoard()->getPieceByPos(1, 1);
        $piece2 = $game->getBoard()->getPieceByPos(2, 1);

        $this->assertSame(array($piece1), PieceFilter::filterProjection(array($piece1, $piece2)));
    }

    /**
     * @depends testGameCreation
     */
    public function testFilterClass(Entities\Game $game)
    {
        $piece1 = $game->getBoard()->getPieceByPos(1, 1);
        $piece2 = $game->getBoard()->getPieceByPos(2, 1);

        $this->assertSame(array($piece2), PieceFilter::filterClass(array($piece1, $piece2), 'Knight'));
    }

    /**
     * @depends testGameCreation
     */
    public function testFilterNotClass(Entities\Game $game)
    {
        $piece1 = $game->getBoard()->getPieceByPos(1, 1);
        $piece2 = $game->getBoard()->getPieceByPos(2, 1);

        $this->assertSame(array($piece2), PieceFilter::filterNotClass(array($piece1, $piece2), 'Rook'));
    }
}
