<?php

namespace Bundle\LichessBundle\Tests\Chess;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Entities\Game;

class Manipulator960Test extends ManipulatorAbstractTest
{
    public function getVariant()
    {
        return Game::VARIANT_960;
    }

    /**
     * @dataProvider whiteKingsideProvider
     */
    public function testWhiteKingside($position, $move)
    {
        $this->createGame($position);
        $this->manipulator->move($move);
        $expected = <<<EOF
rnbqkbnr
pppppppp





R    RK
EOF;
        $this->assertDump($expected);
    }

    /**
     * @dataProvider whiteQueensideProvider
     */
    public function testWhiteQueenside($position, $move)
    {
        $this->createGame($position);
        $this->manipulator->move($move);
        $expected = <<<EOF
rnbqkbnr
pppppppp





R    RK
EOF;
        $this->assertDump($expected);
    }

    public function whiteKingsideProvider()
    {
        $position1 = <<<EOF
rnbqkbnr
pppppppp





R    KR
EOF;
        $move1 = 'f1 g1';
        $position2 = <<<EOF
rnbqkbnr
pppppppp





R     KR
EOF;
        $move2 = 'g1 h1';
        $position3 = <<<EOF
rnbqkbnr
pppppppp





RKR
EOF;
        $move3 = 'b1 g1';
        return array(
            array($position1, $move1),
            array($position2, $move2),
            array($position3, $move3)
        );
    }

    public function whiteQueensideProvider()
    {
        $position1 = <<<EOF
rnbqkbnr
pppppppp





RK     R
EOF;
        $move1 = 'b1 a1';
        $position2 = <<<EOF
rnbqkbnr
pppppppp





R     KR
EOF;
        $move2 = 'f1 c1';
        $position3 = <<<EOF
rnbqkbnr
pppppppp





R K    R
EOF;
        $move3 = 'c1 a1';
        return array(
            array($position1, $move1),
            array($position2, $move2),
            array($position3, $move3)
        );
    }
}
