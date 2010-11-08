<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Document\Piece;

class PieceFilter
{

  // remove dead pieces
  public static function filterAlive(array $pieces)
  {
    foreach($pieces as $it => $piece)
    {
      if ($piece->getIsDead())
      {
        unset($pieces[$it]);
      }
    }

    return array_values($pieces);
  }

  // remove alive pieces
  public static function filterDead(array $pieces)
  {
    foreach($pieces as $it => $piece)
    {
      if (!$piece->getIsDead())
      {
        unset($pieces[$it]);
      }
    }

    return array_values($pieces);
  }

  // only return bishop, rook and queen
  public static function filterProjection(array $pieces)
  {
    foreach($pieces as $it => $piece)
    {
      if (!($piece instanceof Piece\Bishop || $piece instanceof Piece\Rook || $piece instanceof Piece\Queen))
      {
        unset($pieces[$it]);
      }
    }

    return array_values($pieces);
  }

  // only keep asked class
  public static function filterClass(array $pieces, $class)
  {
    foreach($pieces as $it => $piece) {
      if (!$piece->isClass($class)) {
        unset($pieces[$it]);
      }
    }

    return array_values($pieces);
  }

  public static function filterType($pieces, $type)
  {
      return $this->filterClass($pieces, ucfirst($type));
  }

  // remove asked class
  public static function filterNotClass(array $pieces, $class)
  {
    foreach($pieces as $it => $piece) {
      if ($piece->isClass($class)) {
        unset($pieces[$it]);
      }
    }

    return array_values($pieces);
  }

  public static function filterNotType(array $pieces, $type)
  {
      return self::filterClass($pieces, ucfirst($type));
  }

  /**
   * Return pieces that never moved
   *
   * @return array of pieces
   **/
  public static function filterNotMoved(array $pieces)
  {
      foreach ($pieces as $index => $piece)
      {
          if($piece->hasMoved()) {
              unset($pieces[$index]);
          }
      }

      return array_values($pieces);
  }
}
