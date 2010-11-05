<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Document\Game;

class PlayTest extends \PHPUnit_Framework_TestCase
{
    protected $game;

    public function test1()
    {
        $game = $this->game = $this->createGame();

        $this->applyMoves(array(
            'e2 e4',
            'e7 e5',
            'f1 c4',
            'g8 f6',
            'd2 d3',
            'c7 c6',
            'c1 g5',
            'h7 h6'
        ));
        $this->assertDump(<<<EOF
rnbqkb r
pp p pp
  p  n p
    p B
  B P
   P
PPP  PPP
RN QK NR
EOF
        );
        $this->applyMoves(array('g5 f6'));
        $this->assertDump(<<<EOF
rnbqkb r
pp p pp
  p  B p
    p
  B P
   P
PPP  PPP
RN QK NR
EOF
        );
        $this->applyMoves(array('d8 f6'));
        $this->assertDump(<<<EOF
rnb kb r
pp p pp
  p  q p
    p
  B P
   P
PPP  PPP
RN QK NR
EOF
        );
    }

    public function testDeepBlueKasparov1()
    {
        $game = $this->game = $this->createGame();

        $this->applyMoves(array(
            'e2 e4',
            'c7 c5',
            'c2 c3',
            'd7 d5',
            'e4 d5'
        ));
        $this->assertDump(<<<EOF
rnbqkbnr
pp  pppp

  pP

  P
PP P PPP
RNBQKBNR
EOF
        );
        $this->applyMoves(array(
            'd8 d5',
            'd2 d4',
            'g8 f6',
            'g1 f3',
            'c8 g4',
            'f1 e2',
            'e7 e6',
            'h2 h3',
            'g4 h5',
            'e1 g1'
        ));
        $this->assertDump(<<<EOF
rn  kb r
pp   ppp
    pn
  pq   b
   P
  P  N P
PP  BPP
RNBQ RK
EOF
        );
        $this->applyMoves(array(
            'b8 c6',
            'c1 e3',
            'c5 d4',
            'c3 d4',
            'f8 b4'
        ));
        $this->assertDump(<<<EOF
r   k  r
pp   ppp
  n pn
   q   b
 b P
    BN P
PP  BPP
RN Q RK
EOF
        );
    }

    public function testPeruvianImmortal()
    {
        $game = $this->game = $this->createGame();

        $this->applyMoves(array(
            'e2 e4',
            'd7 d5',
            'e4 d5',
            'd8 d5',
            'b1 c3',
            'd5 a5',
            'd2 d4',
            'c7 c6',
            'g1 f3',
            'c8 g4',
            'c1 f4',
            'e7 e6',
            'h2 h3',
            'g4 f3',
            'd1 f3',
            'f8 b4',
            'f1 e2',
            'b8 d7',
            'a2 a3',
            'e8 c8'
        ));
        $this->assertDump(<<<EOF
  kr  nr
pp n ppp
  p p
q
 b P B
P N  Q P
 PP BPP
R   K  R
EOF
        );
        $this->applyMoves(array(
            'a3 b4',
            'a5 a1',
            'e1 d2',
            'a1 h1',
            'f3 c6',
            'b7 c6',
            'e2 a6'
        ));
        $this->assertDump(<<<EOF
  kr  nr
p  n ppp
B p p

 P P B
  N    P
 PPK PP
       q
EOF
        );
        $this->assertTrue($this->game->getIsFinished());
        $this->assertTrue($this->game->getPlayer('white')->getIsWinner());
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
        $manipulator = new Manipulator($this->game);
        foreach ($moves as $move)
        {
            $manipulator->play($move);
        }
    }

    /**
     * Get a game from visual data block
     *
     * @return Game
     **/
    protected function createGame($data = null)
    {
        $generator = new Generator();
        if ($data) {
            $game = $generator->createGameFromVisualBlock($data);
        }
        else {
            $game = $generator->createGame();
        }
        $game->setStatus(Game::STARTED);
        return $game;
    }
}
