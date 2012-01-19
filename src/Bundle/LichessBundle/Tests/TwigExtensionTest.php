<?php

namespace Bundle\LichessBundle\Tests;

use Bundle\LichessBundle\Twig\LichessExtension;

class TwigExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider timeProvider
     */
    public function testAnimationDelayFactor($time, $factor)
    {
        $this->assertEquals($factor, LichessExtension::animationDelayFactor($time));
    }

    public function timeProvider()
    {
        return array(
            array(60, 0.2),
            array(120, 0.2),
            array(180, 0.4),
            array(240, 0.6),
            array(300, 0.8),
            array(360, 1),
            array(420, 1.2),
            array(480, 1.4),
            array(2000, 1.4)
        );
    }
}
