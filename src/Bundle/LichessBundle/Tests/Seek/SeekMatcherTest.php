<?php

namespace Bundle\LichessBundle\Seek;

class SeekMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testResolveColorRandomRandom()
    {
        $result = $this->getSeekMatcher()->resolveCreatorColor(
            $this->getColoredSeekMock('random'),
            $this->getColoredSeekMock('random')
        );

        $this->assertTrue(in_array($result, array('white', 'black')));
    }

    public function testResolveColorRandomWhite()
    {
        $result = $this->getSeekMatcher()->resolveCreatorColor(
            $this->getColoredSeekMock('random'),
            $this->getColoredSeekMock('white')
        );

        $this->assertEquals('black', $result);
    }

    public function testResolveColorRandomBlack()
    {
        $result = $this->getSeekMatcher()->resolveCreatorColor(
            $this->getColoredSeekMock('random'),
            $this->getColoredSeekMock('black')
        );

        $this->assertEquals('white', $result);
    }

    public function testResolveColorWhiteRandom()
    {
        $result = $this->getSeekMatcher()->resolveCreatorColor(
            $this->getColoredSeekMock('white'),
            $this->getColoredSeekMock('random')
        );

        $this->assertEquals('white', $result);
    }

    public function testResolveColorBlackWhite()
    {
        $result = $this->getSeekMatcher()->resolveCreatorColor(
            $this->getColoredSeekMock('black'),
            $this->getColoredSeekMock('white')
        );

        $this->assertEquals('black', $result);
    }

    /**
     * @expectedException LogicException
     */
    public function testResolveColorBlackBlack()
    {
        $result = $this->getSeekMatcher()->resolveCreatorColor(
            $this->getColoredSeekMock('black'),
            $this->getColoredSeekMock('black')
        );
    }

    protected function getSeekMatcher()
    {
        return new SeekMatcher();
    }

    protected function getColoredSeekMock($color)
    {
        $seek = $this->getMockBuilder('Bundle\LichessBundle\Document\Seek')
            ->disableOriginalConstructor()
            ->setMethods(array('getColor'))
            ->getMock();

        $seek->expects($this->atLeastOnce())
            ->method('getColor')
            ->will($this->returnValue($color));

        return $seek;
    }
}
