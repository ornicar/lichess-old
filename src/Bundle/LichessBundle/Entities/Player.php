<?php

namespace Bundle\LichessBundle\Entities;

/**
 * Represents a single Chess player for one game
 *
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class Player
{
    /**
     * Unique hash of the player
     *
     * @var string
     */
    protected $hash;

    /**
     * the player color, white or black
     *
     * @var string
     */
    protected $color = null;

    /**
     * the player current game
     *
     * @var Game
     */
    protected $game = null;
    
    /**
     * the player pieces
     *
     * @var array
     */
    protected $pieces = array();

    /**
     * Whether the player won the game or not
     *
     * @var boolean
     */
    protected $isWinner = false;

    /**
     * Whether this player is an Artificial intelligence or not
     *
     * @var boolean
     */
    protected $isAi = false;

    public function __construct($color)
    {
        $this->color = $color;
        $this->hash = substr(\sha1(\uniqid().\mt_rand().microtime(true)), 0, 4);
    }

    /**
     * @return Piece\King
     */
    public function getKing()
    {
        foreach($this->pieces as $piece) {
            if($piece instanceof \Bundle\LichessBundle\Entities\Piece\King) {
                return $piece;
            }
        }
    }

    /**
     * @return array
     */
    public function getPiecesByClass($class) {
        $class = '\\Bundle\\LichessBundle\\Entities\\Piece\\'.$class;
        $pieces = array();
        foreach($this->pieces as $piece) {
            if($piece instanceof $class) {
                $pieces[] = $piece;
            }
        }
        return $pieces;
    }
    
    /**
     * @return boolean
     */
    public function getIsAi()
    {
      return $this->isAi;
    }
    
    /**
     * @param boolean
     */
    public function setIsAi($isAi)
    {
      $this->isAi = $isAi;
    }
    
    
    /**
     * @return boolean
     */
    public function getIsWinner()
    {
      return $this->isWinner;
    }
    
    /**
     * @param boolean
     */
    public function setIsWinner($isWinner)
    {
      $this->isWinner = $isWinner;
    }
    
    /**
     * @return array
     */
    public function getPieces()
    {
      return $this->pieces;
    }
    
    /**
     * @param array
     */
    public function setPieces($pieces)
    {
      $this->pieces = $pieces;
    }
    
    /**
     * @return Game
     */
    public function getGame()
    {
      return $this->game;
    }
    
    /**
     * @param Game
     */
    public function setGame($game)
    {
      $this->game = $game;
    }

    /**
     * @return string
     */
    public function getColor()
    {
      return $this->color;
    }
    
    /**
     * @param string
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    public function getOpponent()
    {
        return $this->getGame()->getPlayer('white' === $this->getColor() ? 'black' : 'white');
    }

    public function getIsMyTurn()
    {
        return $this->getGame()->getTurns() %2 xor 'white' === $this->getColor();
    }
}
