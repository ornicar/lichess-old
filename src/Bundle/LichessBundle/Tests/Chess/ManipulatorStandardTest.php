<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Model\Game;

class ManipulatorStandardTest extends ManipulatorAbstractTest
{
    public function getVariant()
    {
        return Game::VARIANT_STANDARD;
    }
}
