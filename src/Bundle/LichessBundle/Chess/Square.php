<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Entities\Player;

class Square
{
  protected
  $board,
  $x,
  $y,
  $key,
  $color;
  
  public function __construct(Board $board, $key, $x, $y, $color)
  {
    $this->board  = $board;
    $this->key    = $key;
    $this->x      = $x;
    $this->y      = $y;
    $this->color  = $color;
  }
  
  public function getSquareByRelativePos($x, $y)
  {
    return $this->getBoard()->getSquareByPos($this->x+$x, $this->y+$y);
  }
  
  public function getPiece()
  {
    return $this->getBoard()->getPieceByKey($this->key);
  }

  public function isEmpty()
  {
    return !$this->getPiece();
  }

  public function isControlledBy(Player $player)
  {
    return in_array($this->key, $player->getControlledKeys());
  }

  /*
   * Basic accessors
   */

  public function __toString()
  {
    return 'Square '.$this->getHumanPos();
  }
  
  public function toDebug()
  {
    return $this->__toString();
  }
  
  public function getBoard()
  {
    return $this->board;
  }
  
  public function getX()
  {
    return $this->x;
  }

  public function getY()
  {
    return $this->y;
  }

  public function getKey()
  {
    return $this->key;
  }

  public function getHumanPos()
  {
    return $this->board->keyToHumanPos($this->key);
  }

  public function getColor()
  {
    return $this->color;
  }

  public function isWhite()
  {
    return 'white' === $this->color;
  }

  public function isBlack()
  {
    return 'black' === $this->color;
  }

  public function is(Square $square)
  {
    return $this->key === $square->getKey();
  }
}
