<?php

namespace Bundle\LichessBundle\Tests;

require_once __DIR__.'/Entities/GameTest.php';
require_once __DIR__.'/Entities/PlayerTest.php';
require_once __DIR__.'/Persistence/FilePersistenceTest.php';
require_once __DIR__.'/Chess/GeneratorTest.php';
require_once __DIR__.'/Chess/BoardTest.php';
require_once __DIR__.'/Chess/SquareTest.php';
require_once __DIR__.'/Chess/PieceFilterTest.php';

class AllTests
{
  public static function suite()
  {
    $suite = new \PHPUnit_Framework_TestSuite('LichessBundle');

    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Entities\GameTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Entities\PlayerTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Persistence\FilePersistenceTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Chess\GeneratorTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Chess\BoardTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Chess\SquareTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Chess\PieceFilterTest');

    return $suite;
  }
}
