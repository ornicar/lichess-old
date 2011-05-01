<?php

namespace Bundle\LichessBundle\Seek;

class SeekMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getIncrements
     */
    public function testGetCommonIncrement(array $incrementsA, array $incrementsB, array $possibleResults)
    {
        $matcher = new SeekMatcher(false);

        $seekA = $this->getMockBuilder('Bundle\LichessBundle\Document\Seek')
            ->disableOriginalConstructor()
            ->getMock();
        $seekA->expects($this->atLeastOnce())
            ->method('getIncrements')
            ->will($this->returnValue($incrementsA));

        $seekB = $this->getMockBuilder('Bundle\LichessBundle\Document\Seek')
            ->disableOriginalConstructor()
            ->getMock();
        $seekB->expects($this->atLeastOnce())
            ->method('getIncrements')
            ->will($this->returnValue($incrementsB));

        $result = $matcher->getCommonIncrement($seekA, $seekB);
        $this->assertContains($result, $possibleResults);
    }

    public function getIncrements()
    {
        return array(
            array(array(0), array(0), array(0)),
            array(array(0), array(2), array(0, 2)),
            array(array(2), array(0), array(0, 2)),
            array(array(0, 2), array(0, 2), array(0, 2)),
            array(array(2), array(2), array(2)),
            array(array(0, 2, 5), array(5), array(5)),
            array(array(0), array(20), array(0, 20))
        );
    }
}
