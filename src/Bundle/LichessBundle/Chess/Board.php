<?php

namespace Bundle\LichessBundle\Chess;

use Bundle\LichessBundle\Entities\Game;

class Board
{
  protected
  $game,
  $squares,
  $cache;
  
  public function __construct(Game $game)
  {
    $this->game = $game;
    $this->createSquares();
    $this->compile();
  }
  
  public function compile()
  {
      $this->cache = array_fill(1, 8, array_fill(1, 8, null));

      foreach($this->getPieces() as $piece)
      {
          $this->cache[$piece->getSquareKey()] = $piece;
      }
  }
  
  public function getGame()
  {
    return $this->game;
  }
  
  public function getPlayers()
  {
    return $this->game->getPlayers();
  }
  
  public function getPieces()
  {
    return $this->game->getPieces();
  }
  
  public function getSquares()
  {
    return $this->squares;
  }
  
  public function getSquareKeys()
  {
    return array_keys($this->squares);
  }

  public function getSquareByKey($key)
  {
    return isset($this->squares[$key]) ? $this->squares[$key] : null;
  }
  
  public function getSquareByPos($x, $y)
  {
    return $this->getSquareByKey('s'.$x.$y);
  }
  
  public function getPieceByKey($key)
  {
      return isset($this->cache[$key]) ? $this->cache[$key] : null;
  }
  
  public function getPieceByPos($x, $y)
  {
    return $this->getPieceByKey('s'.$x.$y);
  }
  public function getPieceByHumanPos($humanPos)
  {
    return $this->getPieceByKey($this->humanPosToKey($humanPos));
  }
  
  protected function createSquares()
  {
    $this->squares = array();
    
    for($x=1; $x<9; $x++)
    {
      for($y=1; $y<9; $y++)
      {
          $key = 's'.$x.$y;
          $color = ($x+$y)%2 ? 'white' : 'black';
          $this->squares[$key] = new Square($this, $key, $x, $y, $color);
      }
    }
  }

  public function squaresToKeys(array $squares)
  {
    $keys = array();
    foreach($squares as $square)
    {
      $keys[] = $square->getKey();
    }
    return $keys;
  }

  /**
   * removes non existing or duplicated square
   */
  public function cleanSquares(array $squares, $passedKeys = array())
  {
    foreach($squares as $it => $square)
    {
      if($square instanceof Square)
      {
        $key = $square->getKey();
      }
      else
      {
        unset($squares[$it]);
        continue;
      }
      
      if(in_array($key, $passedKeys))
      {
        unset($squares[$it]);
      }
      else
      {
        $passedKeys[] = $key;
      }
    }
    
    return array_values($squares);
  }
  
  public function humanPosToKey($pos)
  {
    if(is_array($pos))
    {
      foreach($pos as $i => $p)
      {
        $pos[$i] = $this->humanPosToKey($p);
      }
      return $pos;
    }
    
    $letters = 'abcdefgh';
    return 's'.(1+strpos($letters, $pos{0})).$pos{1};
  }
  
  public function keyToHumanPos($key)
  {
    if(is_array($key))
    {
      foreach($key as $i => $k)
      {
        $key[$i] = $this->keyToHumanPos($k);
      }
      return $key;
    }
    
    $letters = 'abcdefgh';
    return $letters{$key{1}-1}.$key{2};
  }
}
