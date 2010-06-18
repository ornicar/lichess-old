<?php

namespace Bundle\LichessBundle\Tests;

require_once __DIR__.'/Entities/GameTest.php';
require_once __DIR__.'/Entities/PlayerTest.php';
require_once __DIR__.'/Persistence/FilePersistenceTest.php';
require_once __DIR__.'/Chess/GeneratorTest.php';
require_once __DIR__.'/Chess/BoardTest.php';
require_once __DIR__.'/Chess/SquareTest.php';
require_once __DIR__.'/Chess/PieceFilterTest.php';
require_once __DIR__.'/Chess/AnalyserTest.php';
require_once __DIR__.'/Chess/ManipulatorTest.php';
require_once __DIR__.'/Chess/PossibleMovesTest.php';
require_once __DIR__.'/Chess/CastlingTest.php';
require_once __DIR__.'/Chess/PromotionTest.php';
require_once __DIR__.'/Chess/EnPassantTest.php';
require_once __DIR__.'/Chess/PlayTest.php';
require_once __DIR__.'/Piece/PawnTest.php';
require_once __DIR__.'/Piece/RookTest.php';
require_once __DIR__.'/Piece/KnightTest.php';
require_once __DIR__.'/Piece/BishopTest.php';
require_once __DIR__.'/Piece/QueenTest.php';
require_once __DIR__.'/Piece/KingTest.php';
require_once __DIR__.'/Ai/StupidTest.php';
require_once __DIR__.'/Ai/CraftyTest.php';
require_once __DIR__.'/Socket/SocketTest.php';
require_once __DIR__.'/Stack/StackTest.php';
require_once __DIR__.'/Notation/ForsytheTest.php';

class AllTests
{
  public static function suite()
  {
    $suite = new \PHPUnit_Framework_TestSuite('LichessBundle');

    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Entities\GameTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Entities\PlayerTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Piece\PawnTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Piece\RookTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Piece\KnightTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Piece\BishopTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Piece\QueenTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Piece\KingTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Persistence\FilePersistenceTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Chess\GeneratorTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Chess\BoardTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Chess\SquareTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Chess\PieceFilterTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Chess\AnalyserTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Chess\ManipulatorTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Chess\PossibleMovesTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Chess\CastlingTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Chess\PromotionTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Chess\EnPassantTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Chess\PlayTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Ai\StupidTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Ai\CraftyTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Socket\SocketTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Stack\StackTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Notation\ForsytheTest');

    return $suite;
  }
}
