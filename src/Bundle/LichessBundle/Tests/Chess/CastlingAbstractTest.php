<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Entities\Game;

abstract class CastlingAbstractTest extends \PHPUnit_Framework_TestCase
{
    protected $game;
    protected $analyzer;

    abstract public function getVariant();

    public function testInitialPosition()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp




PPPPPPPP
RNBQKBNR
EOF;
        $this->createGame($data);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertMoves('e1', null);
    }

    public function testPossibleWhite()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp





R   K  R
EOF;
        $this->createGame($data);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertMoves('e1', 'd1 d2 e2 f2 f1 c1 g1');
    }

    public function testPossibleWhite2()
    {
        $data = <<<EOF
  bqkb r
p ppp pp
pr
   P p
   QnB
 PP  N
P    PPP
RN  K  R
EOF;
        $this->createGame($data);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertMoves('e1', 'd1 e2 f1 g1');
    }

    public function testPossibleBlack()
    {
        $data = <<<EOF
r   k  r





PPPPPPPP
RNBQKBNR
EOF;
        $this->createGame($data, true);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertMoves('e8', 'd8 d7 e7 f7 f8 c8 g8');
    }

    public function testPossibleBlackPeruvian()
    {
        $data = <<<EOF
r   k nr
pp n ppp
  p p
q
 b P B
P N  Q P
 PP BPP
R   K  R
EOF;
        $this->createGame($data, true);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertMoves('e8', 'c8 d8 e7 f8');
    }

    public function testFromCheckWhite()
    {
        $data = <<<EOF
rnbqkbn
pppppppp



    r

R   K  R
EOF;
        $this->createGame($data);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertMoves('e1', 'd1 d2 f2 f1');
    }

    public function testFromCheckBlack()
    {
        $data = <<<EOF
r   k  r

    R



PPPPPPPP
RNBQKBNR
EOF;
        $this->createGame($data, true);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertMoves('e8', 'd8 d7 f7 f8');
    }

    public function testThroughtCheckQueenSideWhite()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp


      b


R   K  R
EOF;
        $this->createGame($data);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertMoves('e1', 'd2 f2 f1 g1');
    }

    public function testThroughtCheckQueenSideBlack()
    {
        $data = <<<EOF
r   k  r


      B


PPPPPPPP
RNBQKBNR
EOF;
        $this->createGame($data, true);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertMoves('e8', 'd7 f7 f8 g8');
    }

    public function testThroughtCheckKingSideWhite()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp


  b


R   K  R
EOF;
        $this->createGame($data);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertMoves('e1', 'd1 d2 f2 c1');
    }

    public function testThroughtCheckKingSideBlack()
    {
        $data = <<<EOF
r   k  r


  B


PPPPPPPP
RNBQKBNR
EOF;
        $this->createGame($data, true);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertMoves('e8', 'd8 d7 f7 c8');
    }

    public function testThroughtCheckBothSideWhite()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp



    n

R   K  R
EOF;
        $this->createGame($data);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertMoves('e1', 'd2 e2 f2');
    }

    public function testThroughtCheckBothSideBlack()
    {
        $data = <<<EOF
r   k  r

    N



PPPPPPPP
RNBQKBNR
EOF;
        $this->createGame($data, true);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertMoves('e8', 'd7 e7 f7');
    }

    public function testToCheckQueenSideWhite()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp


     b


R   K  R
EOF;
        $this->createGame($data);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertMoves('e1', 'd1 e2 f2 f1 g1');
    }

    public function testToCheckKingSideWhite()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp


   b


R   K  R
EOF;
        $this->createGame($data);
        $this->assertCanCastleQueenSide(true);
        $this->assertCanCastleKingSide(true);
        $this->assertMoves('e1', 'd1 d2 e2 f1 c1');
    }

    public function testMovedRookWhite()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp





R   K  R
EOF;
        $this->createGame($data);
        foreach($this->game->getTurnPlayer()->getPieces() as $piece) {
            if('Rook' === $piece->getClass()) {
                $piece->setFirstMove(4);
            }
        }
        $this->assertCanCastleQueenSide(false);
        $this->assertCanCastleKingSide(false);
        $this->assertMoves('e1', 'd1 d2 e2 f2 f1');
    }

    public function testMovedKingWhite()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp





R   K  R
EOF;
        $this->createGame($data);
        $this->game->getTurnPlayer()->getKing()->setFirstMove(4);
        $this->assertCanCastleQueenSide(false);
        $this->assertCanCastleKingSide(false);
        $this->assertMoves('e1', 'd1 d2 e2 f2 f1');
    }

    /**
     * Get a game from visual data block
     *
     * @return Game
     **/
    protected function createGame($data, $blackTurn = false)
    {
        $generator = new Generator();
        $this->game = $generator->createGameFromVisualBlock($data);
        $this->game->setVariant($this->getVariant());
        $this->game->setStatus(Game::STARTED);
        $this->game->setTurns($blackTurn ? 11 : 10);
        $this->analyser = new Analyser($this->game->getBoard());
    }

    protected function assertMoves($pieceKey, $moves)
    {
        $moves = empty($moves) ? null : explode(' ', $moves);
        $possibleMoves = $this->analyser->getPlayerPossibleMoves($this->game->getTurnPlayer());
        $this->assertEquals($this->sort($moves), isset($possibleMoves[$pieceKey]) ? $this->sort($possibleMoves[$pieceKey]) : null);
    }

    protected function assertCanCastleKingSide($bool)
    {
        $player = $this->game->getTurnPlayer();
        $canCastleKingSide = $this->analyser->canCastleKingSide($player);
        $this->assertEquals($bool, $canCastleKingSide);
    }

    protected function assertCanCastleQueenSide($bool)
    {
        $player = $this->game->getTurnPlayer();
        $canCastleQueenSide = $this->analyser->canCastleQueenSide($player);
        $this->assertEquals($bool, $canCastleQueenSide);
    }

    protected function sort($array)
    {
        if(is_array($array)) {
            sort($array);
        }
        return $array;
    }
}
