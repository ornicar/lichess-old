<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Entities\Game;

require_once __DIR__.'/../gameBootstrap.php';
require_once __DIR__.'/../../Chess/Manipulator.php';

class CastlingTest extends \PHPUnit_Framework_TestCase
{
    protected $game;
    protected $analyzer;

    public function testPossibleWhite()
    {
        $data = <<<EOF
rnbqkbnr
pppppppp
        
        
        
        
        
R   K  R
EOF;
        $this->createGame($data);
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
        $this->assertMoves('e1', 'd1 d2 e2 f1 c1');
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
        $this->game->setStatus(Game::STARTED);
        $this->game->setTurns($blackTurn ? 11 : 10);
        $this->analyser = new Analyser($this->game->getBoard());
    }

    protected function assertMoves($pieceKey, $moves)
    {
        $moves = explode(' ', $moves);
        $possibleMoves = $this->analyser->getPlayerPossibleMoves($this->game->getTurnPlayer());
        $this->assertEquals($this->sort($moves), isset($possibleMoves[$pieceKey]) ? $this->sort($possibleMoves[$pieceKey]) : null);
    }

    protected function sort($array)
    {
        sort($array);
        return $array;
    }
}
