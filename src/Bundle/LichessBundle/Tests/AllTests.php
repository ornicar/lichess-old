<?php

namespace Bundle\LichessBundle\Tests;

require_once __DIR__.'/Entities/GameTest.php';
require_once __DIR__.'/Entities/PlayerTest.php';
require_once __DIR__.'/Persistence/FilePersistenceTest.php';
require_once __DIR__.'/Chess/GeneratorTest.php';

class AllTests
{
  public static function suite()
  {
    $suite = new \PHPUnit_Framework_TestSuite('LichessBundle');

    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Entities\GameTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Entities\PlayerTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Persistence\FilePersistenceTest');
    $suite->addTestSuite('\Bundle\LichessBundle\Tests\Chess\GeneratorTest');

    return $suite;
  }
}
