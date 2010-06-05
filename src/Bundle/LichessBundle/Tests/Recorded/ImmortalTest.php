<?php

namespace Bundle\LichessBundle\Tests\Recorded;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;

require_once __DIR__.'/../gameBootstrap.php';
require_once __DIR__.'/../../Chess/Manipulator.php';
require_once __DIR__.'/../../Notation/PgnParser.php';

class ImmortalTest extends \PHPUnit_Framework_TestCase
{

    public function testReplay()
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $board = $game->getBoard();
        $manipulator = new Manipulator($board);

        $moves = $this->parse(__DIR__.'/fixtures/immortal.pgn');
        var_dump($moves);die;
    }

    protected function parse($file)
    {
        $parser = new \PgnParser($file);
        $parser->__parsePgnString(file_get_contents($file));
        $moveString = $parser->games[0]['moveString'];
        $moves = $parser->getMovesFromMoveString($moveString);

        var_dump($moves);die;
    }
}
