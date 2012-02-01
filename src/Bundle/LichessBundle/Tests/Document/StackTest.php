<?php

namespace Bundle\LichessBundle\Document;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Tests\TestManipulator;
use ArrayObject;

class StackTest extends \PHPUnit_Framework_TestCase
{
    protected $game;

    public function testOptimize()
    {
        $events = array(
            0 => array('type' => 'f'),
            1 => array('type' => 'possible_moves', 'possible_moves' => array('z' => 'ionu')),
            2 => array('type' => 'a'),
            3 => array('type' => 'possible_moves', 'possible_moves' => array('a' => 'ne')),
            4 => array('type' => 'c'),
            5 => array('type' => 'possible_moves', 'possible_moves' => array('c' => 'xy')),
        );
        $stack = new Stack($events);
        $stack->optimize();
        $events = $stack->getEvents();
        $this->assertEquals(6, count($events));
        $expected = array(
            0 => array('type' => 'f'),
            1 => array('type' => 'possible_moves'),
            2 => array('type' => 'a'),
            3 => array('type' => 'possible_moves'),
            4 => array('type' => 'c'),
            5 => array('type' => 'possible_moves', 'possible_moves' => array('c' => 'xy')),
        );
        $this->assertSame($expected, $events);
    }

    public function testMove()
    {
        $this->createGame($this->getData());
        $events = new ArrayObject();
        $manipulator = new TestManipulator($this->game, $events);
        $manipulator->play('a2 a4');
        $this->assertEquals(array(array('type' => 'move', 'from' => 'a2', 'to' => 'a4', 'color' => 'white')), $events->getArrayCopy());
    }

    public function testKill()
    {
        $this->createGame($this->getData());
        $events = new ArrayObject();
        $manipulator = new TestManipulator($this->game, $events);
        $manipulator->play('c3 e4');
        $this->assertEquals(array(array('type' => 'move', 'from' => 'c3', 'to' => 'e4', 'color' => 'white')), $events->getArrayCopy());
    }

    public function testEnPassant()
    {
        $data = <<<EOF



    Pp



k  K
EOF;
        $game = $this->createGame($data);
        $game->setTurns(30);
        $wp = $game->getBoard()->getPieceByKey('e5');
        $bp = $game->getBoard()->getPieceByKey('f5');
        $wp->setFirstMove(12);
        $bp->setFirstMove(29);
        $events = new ArrayObject();
        $manipulator = new TestManipulator($this->game, $events);
        $manipulator->play('e5 f6');
        $this->assertEquals(array(
            array('type' => 'move', 'from' => 'e5', 'to' => 'f6', 'color' => 'white'),
            array('type' => 'enpassant', 'killed' => 'f5')
        ), $events->getArrayCopy());
    }

    public function testCastling()
    {
        $data = <<<EOF
r  q rk
pp  ppbp
 np  np
  Q   B
   pp b
  N  N
PP   PPP
R   K  R
EOF;
        $game = $this->createGame($data);
        $events = new ArrayObject();
        $manipulator = new TestManipulator($this->game, $events);
        $manipulator->play('e1 c1');
        $this->assertEquals(array(
            array('type' => 'move', 'from' => 'e1', 'to' => 'c1', 'color' => 'white'),
            array('type' => 'castling', 'king' => array('e1', 'c1'), 'rook' => array('a1', 'd1'), 'color' => 'white'),
        ), $events->getArrayCopy());
    }

    public function testPromotionQueen()
    {
        $data = <<<EOF

 P     k





K
EOF;
        $game = $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('b7')->setFirstMove(1);
        $events = new ArrayObject();
        $manipulator = new TestManipulator($this->game, $events);
        $manipulator->play('b7 b8', array('promotion' => 'Queen'));
        $this->assertEquals(array(
            array('type' => 'move', 'from' => 'b7', 'to' => 'b8', 'color' => 'white'),
            array('type' => 'promotion', 'pieceClass' => 'queen', 'key' => 'b8')
        ), $events->getArrayCopy());
    }

    public function testPromotionKnight()
    {
        $data = <<<EOF

 P
   k


 r

K
EOF;
        $game = $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('b7')->setFirstMove(1);
        $events = new ArrayObject();
        $manipulator = new TestManipulator($this->game, $events);
        $manipulator->play('b7 b8', array('promotion' => 'Knight'));
        $this->assertEquals(array(
            array('type' => 'move', 'from' => 'b7', 'to' => 'b8', 'color' => 'white'),
            array('type' => 'promotion', 'pieceClass' => 'knight', 'key' => 'b8')
        ), $events->getArrayCopy());
    }

    public function testCheck()
    {
        $data = <<<EOF

 Q
   k




K
EOF;
        $game = $this->createGame($data);
        $this->game->getBoard()->getPieceByKey('b7')->setFirstMove(1);
        $events = new ArrayObject();
        $manipulator = new TestManipulator($this->game, $events);
        $manipulator->play('b7 b6', array('promotion' => 'Knight'));
        $this->assertEquals(array(
            array('type' => 'move', 'from' => 'b7', 'to' => 'b6', 'color' => 'white'),
            array('type' => 'check', 'key' => 'd6')
        ), $events->getArrayCopy());
    }

    protected function getData()
    {
        $data = <<<EOF
r  q rk
pp  ppbp
 np  np
  Q   B
   pp b
  N  N
PP   PPP
R   KB R
EOF;
        return $data;
    }

    /**
     * Get a game from visual data block
     *
     * @return Game
     **/
    protected function createGame($data)
    {
        $generator = new Generator();
        $this->game = $generator->createGameFromVisualBlock($data);
        return $this->game;
    }
}
