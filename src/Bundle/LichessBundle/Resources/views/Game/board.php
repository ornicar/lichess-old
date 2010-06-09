<?php

$game = $player->getGame();
$squares = $game->getBoard()->getSquares();

if ($player->isBlack())
{
  $squares = array_reverse($squares, true);
}

$x = $y = 1;

print '<div class="lichess_board">';

foreach($squares as $squareKey => $square)
{
  $piece = $square->getPiece();
  
  $check = $checkSquareKey === $squareKey ? ' check' : '';

  printf('<div class="lichess_square %s%s" id="%s" style="top:%dpx;left:%dpx;">', $square->getColor(), $check, $squareKey, 64*(8-$x), 64*($y-1));

    print '<div class="lcsi">';

    if($piece) {
      printf('<div class="lichess_piece %s %s" id="%s"></div>',
        strtolower($piece->getClass()), $piece->getColor(), $piece->getHash()
      );
    }

    print '</div>';

  print '</div>';
  
  if (++$x === 9)
  {
    $x = 1; ++$y;
  }
}

print '</div>';
