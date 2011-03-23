<?php

namespace Bundle\LichessBundle\Tests\Notation;

use Bundle\LichessBundle\Notation\PgnParser;

class PgnParserTest extends \PHPUnit_Framework_TestCase
{

    public function testParseShort()
    {
        $pgn = '1. e4 e5 2. Nf3 Nc6 3. Bb5 {This opening is called the Ruy Lopez.} 3... a6
            4. Ba4 Nf6 5. O-O Be7 6. Re1 b5 7. Bb3 d6 8. c3 O-O 9. h3 Nb8  10. d4 Nbd7';
        $parser = new PgnParser($pgn);
        $moves = $parser->parse();
        $expected  = array('e2 e4', 'e7 e5', 'g1 f3', 'b8 c6', 'f1 b5', 'a7 a6', 'b5 a4', 'g8 f6');
    }
}
