<?php

namespace Bundle\LichessBundle\Ai;

use Bundle\LichessBundle\Ai;

class Stupid extends Ai
{

  public function move()
  {
    $moveTree = $this->player->getPossibleMoves();
    
    // choose random piece
    do
    {
      $from = array_rand($moveTree);
    }
    while(empty($moveTree[$from]));
    
    // choose random move
    $to = $moveTree[$from][array_rand($moveTree[$from])];
    
    return $from.' '.$to;
  }
}
