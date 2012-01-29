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
            array(60, 0),
            array(120, 0),
            array(180, 0.2),
            array(240, 0.4),
            array(300, 0.6),
            array(360, 0.8),
            array(420, 1),
            array(480, 1.2),
            array(540, 1.2),
            array(2000, 1.2)
        );
    }
}
