<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Tests\ChessTest;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\PieceFilter;
use Bundle\LichessBundle\Model\Game;

class PromotionTest extends ChessTest
{
    protected $game;
    protected $analyser;

    public function testPromotionQueen()
    {
        $data = <<<EOF
       k
 P





K
EOF;
        $game = $this->game = $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('b7')->setFirstMove(1);
        $this->move('b7 b8', array('promotion' => 'Queen'));
        $this->assertTrue($this->game->getBoard()->getPieceByKey('b8')->isClass('Queen'));
        $this->assertTrue($this->analyser->isKingAttacked($this->game->getPlayer('black')));
        $this->assertEquals(0, count(PieceFilter::filterClass($this->game->getPlayer('white')->getPieces(), 'Pawn')));
        $this->assertEquals(1, count(PieceFilter::filterClass($this->game->getPlayer('white')->getPieces(), 'Queen')));
    }

    public function testPromotionKnight()
    {
        $data = <<<EOF

 P k





K
EOF;
        $game = $this->game = $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('b7')->setFirstMove(1);
        $this->move('b7 b8', array('promotion' => 'Knight'));
        $this->assertTrue($this->game->getBoard()->getPieceByKey('b8')->isClass('Knight'));
        $this->assertTrue($this->analyser->isKingAttacked($this->game->getPlayer('black')));
        $this->assertEquals(0, count(PieceFilter::filterClass($this->game->getPlayer('white')->getPieces(), 'Pawn')));
        $this->assertEquals(1, count(PieceFilter::filterClass($this->game->getPlayer('white')->getPieces(), 'Knight')));
    }

    public function testNoPromotion()
    {
        $data = <<<EOF
       k
 P





K
EOF;
        $game = $this->game = $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('b7')->setFirstMove(1);
        $this->move('a1 a2');
        $this->assertNull($this->game->getBoard()->getPieceByKey('b8'));
        $this->assertFalse($this->analyser->isKingAttacked($this->game->getPlayer('black')));
        $this->assertEquals(1, count(PieceFilter::filterClass($this->game->getPlayer('white')->getPieces(), 'Pawn')));
        $this->assertEquals(0, count(PieceFilter::filterClass($this->game->getPlayer('white')->getPieces(), 'Knight')));
    }

    /**
     * Verify the game state
     *
     * @return void
     **/
    protected function assertDump($dump)
    {
        $dump = "\n".$dump."\n";
        $this->assertEquals($dump, $this->game->getBoard()->dump());
    }

    /**
     * apply moves
     **/
    protected function applyMoves(array $moves)
    {
        foreach ($moves as $move)
        {
            $this->move($move);
        }
    }

    /**
     * Moves a piece and increment game turns
     *
     * @return void
     **/
    protected function move($move, array $options = array())
    {
        $manipulator = $this->getManipulator($this->game);
        $manipulator->play($move, $options);
    }

    /**
     * Get a game from visual data block
     *
     * @return Game
     **/
    protected function createGame($data = null)
    {
        $generator = $this->getGenerator();
        if ($data) {
            $game = $generator->createGameFromVisualBlock($data);
        }
        else {
            $game = $generator->createGame();
        }
        $this->analyser = new Analyser($game->getBoard());
        $game->setStatus(Game::STARTED);
        $game->setTurns(30);
        return $game;
    }
}
