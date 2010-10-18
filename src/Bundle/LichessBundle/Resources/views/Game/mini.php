<?php

$player = $game->getCreator();
$board = $player->getGame()->getBoard();
$squares = $board->getSquares();

if ($player->isBlack()) {
    $squares = array_reverse($squares, true);
}

$x = $y = 1;

printf('<a href="%s" title="%s" class="mini_board notipsy">',
    $view['router']->generate('lichess_game', array('hash' => $game->getHash())),
    $view['translator']->_('View in full size')
);

foreach($squares as $squareKey => $square)
{
    printf('<div class="lmcs %s" style="top:%dpx;left:%dpx;">',
        $square->getColor(), 24*(8-$x), 24*($y-1)
    );

    print '<div class="lmcsi"></div>';

    if($piece = $board->getPieceByKey($squareKey)) {
        printf('<div class="lcmp %s %s"></div>',
            strtolower($piece->getClass()), $piece->getColor()
        );
    }

    print '</div>';

    if (++$x === 9) {
        $x = 1; ++$y;
    }
}

print '</a>';
