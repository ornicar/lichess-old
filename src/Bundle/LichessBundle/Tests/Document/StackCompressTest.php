<?php

namespace Bundle\LichessBundle\Tests\Document;

use Bundle\LichessBundle\Document\Stack;

class StackCompressTest extends \PHPUnit_Framework_TestCase
{
    protected $game;

    /**
     * @dataProvider eventsProvider
     */
    public function testCompressOne($event)
    {
        $stack = new Stack(array($event));
        $events = $stack->getEvents();
        $newStack = Stack::extract(Stack::compress($stack));
        $newEvents = $newStack->getEvents();
        $this->assertSame($this->sortEventKeys($events), $this->sortEventKeys($newEvents));
    }

    private function sortEventKeys(array $events)
    {
        $sorted = array();
        foreach ($events as $i => $event) {
            ksort($event);
            $sorted[$i] = $event;
        }
        return $sorted;
    }

    public function eventsProvider()
    {
        return array_map(function($event) { return array($event); }, $this->events());
    }

    public function events()
    {
        return array(
            array('type' => 'start'),
            array('type' => 'move', 'from' => 'g4', 'to' => 'c3', 'color' => 'black'),
            array('type' => 'possible_moves', 'possible_moves' => array('a7' => 'a8b8')),
            array('type' => 'possible_moves', 'possible_moves' => array('a2' => 'a3a4', 'f3' => 'f5g3d4e8')),
            array('type' => 'move', 'from' => 'e5', 'to' => 'f6', 'color' => 'white'),
            array('type' => 'enpassant', 'killed' => 'f5'),
            array('type' => 'move', 'from' => 'e1', 'to' => 'c1', 'color' => 'white'),
            array('type' => 'castling', 'king' => array('e1', 'c1'), 'rook' => array('a1', 'd1'), 'color' => 'white'),
            array('type' => 'redirect', 'url' => 'http://en.lichess.org/arstheien'),
            array('type' => 'move', 'from' => 'b7', 'to' => 'b8', 'color' => 'white'),
            array('type' => 'promotion', 'pieceClass' => 'queen', 'key' => 'b8'),
            array('type' => 'move', 'from' => 'b7', 'to' => 'b6', 'color' => 'white'),
            array('type' => 'check', 'key' => 'd6'),
            array('type' => 'message', 'message' => array('foo', 'http://foto.mail.ru/mail/annabuut/_myphoto/631.html#1491')),
            array('type' => 'message', 'message' => array('0x1', 'я слишком красив, чтобы ты это видела=)')),
            //array('type' => 'message', 'message' => array('0x1', 'играл ща с типом Robert_D_James (рейтинг чуть более 1500), так он мне проигрывал...Обазвал меня идиотом просто так и вышел из игры)))')),
            array('type' => 'message', 'message' => array('Thibault', 'Yo ! message')),
        );
    }

    public function testCompressAll()
    {
        $stack = new Stack($this->events());
        $stack->optimize();
        $events = $stack->getEvents();
        $newStack = Stack::extract(Stack::compress($stack));
        $newEvents = $newStack->getEvents();
        $this->assertSame($this->sortEventKeys($events), $this->sortEventKeys($newEvents));
    }
}
