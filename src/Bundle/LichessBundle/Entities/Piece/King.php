<?php

namespace Bundle\LichessBundle\Entities\Piece;
use Bundle\LichessBundle\Entities\Piece;

class King extends Piece
{
  public function getClass()
  {
    return 'King';
  }

  protected function getBasicTargetSquares()
  {
    return array();
  }

  public function isAttacked()
  {
    if($this->hasCache('is_attacked'))
    {
      return $this->getCache('is_attacked');
    }

    return $this->setCache('is_attacked', $this->getGame()->getIsStarted() && $this->getSquare()->isControlledBy($this->getPlayer()->getOpponent()));
  }

}
