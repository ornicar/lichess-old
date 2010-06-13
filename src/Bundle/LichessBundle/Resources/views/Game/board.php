<?php

$board = $player->getGame()->getBoard();
$squares = $board->getSquares();

if ($player->isBlack())
{
  $squares = array_reverse($squares, true);
}

$x = $y = 1;

print '<div class="lichess_board">';

foreach($squares as $squareKey => $square)
{
  printf('<div class="lichess_square %s%s" id="%s" style="top:%dpx;left:%dpx;">',
      $square->getColor(), $checkSquareKey === $squareKey ? ' check' : '', $squareKey, 64*(8-$x), 64*($y-1)
  );

    print '<div class="lcsi"></div>';

    if($piece = $board->getPieceByKey($squareKey)) {
      printf('<div class="lichess_piece %s %s"></div>',
        strtolower($piece->getClass()), $piece->getColor()
      );
    }

  print '</div>';
  
  if (++$x === 9)
  {
    $x = 1; ++$y;
  }
}

print '</div>';
