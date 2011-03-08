<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\PieceFilter;
use Bundle\LichessBundle\Tests\TestManipulator;
use Bundle\LichessBundle\Document\Game;

class AutoDrawTest extends \PHPUnit_Framework_TestCase
{
    protected $game;

    public function testNewGameIsNotDraw()
    {
        $generator = new Generator();
        $game = $generator->createGame();
        $this->assertFalse($this->getAutodraw()->isAutodraw($game));
    }

    public function testPlayedGameIsNotDraw()
    {
        $game = $this->game = $this->createGame();

        $this->applyMoves(array(
            'e2 e4',
            'c7 c5',
            'c2 c3',
            'd7 d5',
            'e4 d5'
        ));
        $this->assertFalse($this->getAutodraw()->isAutodraw($game));
    }

    public function testEndGameIsDraw()
    {
        $data = <<<EOF
       k
       P





K
EOF;
        $game = $this->game = $this->createGame($data);
        $game->setTurns(41);
        $this->assertFalse($this->getAutodraw()->isAutodraw($game));
        $this->move('h8 h7');
        $this->assertTrue($this->getAutodraw()->isAutodraw($game));
    }

    public function testFewMaterialIsDraw()
    {
        $data = <<<EOF
       k




B

K
EOF;
        $game = $this->game = $this->createGame($data);
        $game->setTurns(41);
        $this->assertTrue($this->getAutodraw()->isAutodraw($game));
    }

    public function testFewMaterialIsDraw2()
    {
        $data = <<<EOF
       k




 Nb

K
EOF;
        $game = $this->game = $this->createGame($data);
        $game->setTurns(41);
        $this->assertTrue($this->getAutodraw()->isAutodraw($game));
    }

    public function testFewMaterialWithPawnIsNotDraw()
    {
        $data = <<<EOF
       k




 Np

K
EOF;
        $game = $this->game = $this->createGame($data);
        $game->setTurns(41);
        $this->assertFalse($this->getAutodraw()->isAutodraw($game));
    }

    public function testFewMaterialWithRookIsNotDraw()
    {
        $data = <<<EOF
       k




 Nr

K
EOF;
        $game = $this->game = $this->createGame($data);
        $game->setTurns(41);
        $this->assertFalse($this->getAutodraw()->isAutodraw($game));
    }

    public function testFiftyMovesIsDraw()
    {
        $game = $this->game = $this->createGame();

        $nbMoves = 0;
        do {
            $this->applyMoves(array(
                'b1 c3',
                'b8 c6',
                'c3 b1',
                'c6 b8'
            ));
            $nbMoves += 4;
        } while ($nbMoves < 50);

        $this->assertTrue($this->getAutodraw()->isAutodraw($game));
    }

    /**
     * apply moves
     **/
    protected function applyMoves(array $moves)
    {
        foreach ($moves as $move) {
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
        $manipulator = new TestManipulator($this->game, new \Bundle\LichessBundle\Document\Stack());
        $manipulator->play($move, $options);
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
            $game->setTurns(20);
        }
        else {
            $game = $generator->createGame();
        }
        $game->setStatus(Game::STARTED);
        return $game;
    }

    protected function getAutodraw()
    {
        return new Autodraw();
    }
}
