<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Document\Game;

class CastlingStandardTest extends CastlingAbstractTest
{
    public function getVariant()
    {
        return Game::VARIANT_STANDARD;
    }
}
