<?php

namespace Bundle\LichessBundle\Tests;

require_once __DIR__.'/Functional/startAGameTest.php';
require_once __DIR__.'/Functional/inviteAFriendTest.php';

class AllTests
{
  public static function suite()
  {
    $suite = new \PHPUnit_Framework_TestSuite('LichessBundle_Functional');

    $suite->addTestSuite('Lichess\Tests\Functional\startAGameTest');
    $suite->addTestSuite('Lichess\Tests\Functional\inviteAFriendTest');

    return $suite;
  }
}
