<?php

namespace Bundle\MiamBundle\Tests;

require_once __DIR__.'/Functional/createAProjectTest.php';
require_once __DIR__.'/Functional/showAProjectTest.php';
require_once __DIR__.'/Functional/editAProjectTest.php';
require_once __DIR__.'/Functional/createAStoryTest.php';
require_once __DIR__.'/Functional/showAStoryTest.php';
require_once __DIR__.'/Functional/editAStoryTest.php';

require_once __DIR__.'/Functional/createASprintTest.php';
require_once __DIR__.'/Functional/scheduleASprintTest.php';
require_once __DIR__.'/Functional/showCurrentSprintTest.php';
require_once __DIR__.'/Functional/updateTimelineTest.php';
require_once __DIR__.'/Functional/noSprintAvailableTest.php';
require_once __DIR__.'/Functional/unscheduleAStoryTest.php';

class AllTests
{
  public static function suite()
  {
    $suite = new \PHPUnit_Framework_TestSuite('MiamBundle_Functional');

    $suite->addTestSuite('Miam\Tests\Functional\showAProjectTest');
    $suite->addTestSuite('Miam\Tests\Functional\createAProjectTest');
    $suite->addTestSuite('Miam\Tests\Functional\editAProjectTest');

    $suite->addTestSuite('Miam\Tests\Functional\showAStoryTest');
    $suite->addTestSuite('Miam\Tests\Functional\createAStoryTest');
    $suite->addTestSuite('Miam\Tests\Functional\editAStoryTest');
    $suite->addTestSuite('Miam\Tests\Functional\unscheduleAStoryTest');

    $suite->addTestSuite('Miam\Tests\Functional\createASprintTest');
    $suite->addTestSuite('Miam\Tests\Functional\scheduleASprintTest');
    $suite->addTestSuite('Miam\Tests\Functional\showCurrentSprintTest');
    $suite->addTestSuite('Miam\Tests\Functional\noSprintAvailableTest');
    
    $suite->addTestSuite('Miam\Tests\Functional\updateTimelineTest');

    return $suite;
  }
}
