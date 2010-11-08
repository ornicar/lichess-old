<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Document\Game;

class Castling960Test extends CastlingAbstractTest
{
    public function getVariant()
    {
        return Game::VARIANT_960;
    }

    public function testPossibleWhite960a()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp





   RKR
EOF;
        $this->createGame($data);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertCastleRookQueenSide('d1');
        $this->assertCastleRookKingSide('f1');
        $this->assertMoves('e1', 'c1 d2 e2 f2 g1');
    }

    public function testPossibleWhite960b()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp





RK  R
EOF;
        $this->createGame($data);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertCastleRookQueenSide('a1');
        $this->assertCastleRookKingSide('e1');
        $this->assertMoves('b1', 'a1 a2 b2 c2 c1 g1');
    }

    public function testPossibleWhiteQueen960a()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp





R    KNR
EOF;
        $this->createGame($data);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertCastleRookQueenSide('a1');
        $this->assertCastleRookKingSide('h1');
        $this->assertMoves('f1', 'e1 e2 f2 g2 c1');
    }

    public function testPossibleWhiteQueen960b()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp





RK    NR
EOF;
        $this->createGame($data);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertCastleRookQueenSide('a1');
        $this->assertCastleRookKingSide('h1');
        $this->assertMoves('b1', 'a1 a2 b2 c2 c1');
    }

    public function testPossibleWhiteKing960a()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp





RNK    R
EOF;
        $this->createGame($data);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertCastleRookQueenSide('a1');
        $this->assertCastleRookKingSide('h1');
        $this->assertMoves('c1', 'b2 c2 d2 d1 g1');
    }

    public function testImpossibleWhiteKing960()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp





R    NKR
EOF;
        // the knight is inside the final position range
        $this->createGame($data);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertCastleRookQueenSide('a1');
        $this->assertCastleRookKingSide('h1');
        $this->assertMoves('g1', 'h2 g2 f2');
    }

    public function testImpossibleWhiteKing960b()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp





RK N   R
EOF;
        // the knight is inside the final position range
        $this->createGame($data);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertCastleRookQueenSide('a1');
        $this->assertCastleRookKingSide('h1');
        $this->assertMoves('b1', 'a2 b2 c2 c1');
    }

    public function testPossibleBlack960a()
    {
        $data = <<<EOF
rk  r





PPPPPPPP
RNBQKBNR
EOF;
        $this->createGame($data, true);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertCastleRookQueenSide('a8');
        $this->assertCastleRookKingSide('e8');
        $this->assertMoves('b8', 'a8 a7 b7 c7 c8 g8');
    }

    public function testPossibleBlack960b()
    {
        $data = <<<EOF
    rkr





PPPPPPPP
RNBQKBNR
EOF;
        $this->createGame($data, true);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertCastleRookQueenSide('e8');
        $this->assertCastleRookKingSide('g8');
        $this->assertMoves('f8', 'c8 e7 f7 g7 g8');
    }
}
