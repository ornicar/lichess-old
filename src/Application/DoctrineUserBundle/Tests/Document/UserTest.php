<?php

namespace Application\DoctrineUserBundle\Document;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testMaxElo()
    {
        $user = new User();
        $this->giveEloHistory($user);
        $this->assertEquals(1300, $user->getMaxElo());
    }

    public function testMaxEloDate()
    {
        $user = new User();
        $this->giveEloHistory($user);
        $this->assertEquals(5, $user->getMaxEloDate()->getTimestamp());
    }

    protected function giveEloHistory(User $user)
    {
        $user->setEloHistory(array(
            1 => 1200,
            2 => 1250,
            3 => 1300,
            4 => 1250,
            5 => 1300,
            6 => 1100
        ));
    }
}
