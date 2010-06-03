<?php

namespace Bundle\MiamBundle\Tests;

require_once __DIR__.'/Functional/unscheduleAStoryTest.php';

class AllTests
{
  public static function suite()
  {
    $suite = new \PHPUnit_Framework_TestSuite('MiamBundle_Functional');

    $suite->addTestSuite('Miam\Tests\Functional\updateTimelineTest');

    return $suite;
  }
}
