<?php

namespace Bundle\LichessBundle\Entities;

use Bundle\LichessBundle\Chess\PieceFilter;
use Bundle\LichessBundle\Stack;

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

    /**
     * If the player is an AI, its level represents the AI intelligence
     *
     * @var int
     */
    protected $aiLevel = null;

    /**
     * Event stack
     *
     * @var Stack
     */
    protected $stack = null;
    
    public function __construct($color)
    {
        $this->color = $color;
        $this->hash = '';
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789_';
        for ( $i = 0; $i < 4; $i++ ) {
          $this->hash .= $chars[mt_rand( 0, 36 )];
        }
        $this->stack = new Stack();
        $this->stack->addEvent(array('type' => 'start'));
    }
    
    /**
     * Get stack
     * @return Stack
     */
    public function getStack()
    {
      return $this->stack;
    }
    
    /**
     * Set stack
     * @param  Stack
     * @return null
     */
    public function setStack($stack)
    {
      $this->stack = $stack;
    }
    
    /**
     * @return string
     */
    public function getHash()
    {
      return $this->hash;
    }

    /**
     * @return string
     */
    public function getFullHash()
    {
      return $this->game->getHash().$this->hash;
    }

    /**
     * @return int
     */
    public function getAiLevel()
    {
        return $this->aiLevel;
    }

    /**
     * @param int
     */
    public function setAiLevel($aiLevel)
    {
        $this->aiLevel = $aiLevel;
    }

    /**
     * @return Piece\King
     */
    public function getKing()
    {
        foreach($this->pieces as $piece) {
            if($piece instanceof Piece\King) {
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

    public function getDeadPieces()
    {
        $pieces = array();
        foreach($this->getPieces() as $piece) {
            if($piece->getIsDead()) {
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

    public function addPiece(Piece $piece)
    {
        $this->pieces[] = $piece;
    }

    public function removePiece(Piece $piece)
    {
        $index = array_search($piece, $this->pieces);
        unset($this->pieces[$index]);
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
        return $this->getGame()->getPlayer('white' === $this->color ? 'black' : 'white');
    }

    public function getIsMyTurn()
    {
        return $this->game->getTurns() %2 xor 'white' === $this->color;
    }

    public function isWhite()
    {
        return 'white' === $this->color;
    }

    public function isBlack()
    {
        return 'black' === $this->color;
    }

    public function __toString()
    {
        $string = $this->getColor().' '.($this->getIsAi() ? 'A.I.' : 'Human');

        return $string;
    }

    public function isMyTurn()
    {
        return $this->getGame()->getTurns() %2 ? $this->isBlack() : $this->isWhite();
    }

    public function getBoard()
    {
        return $this->getGame()->getBoard();
    }

    public function serialize()
    {
        return array('hash', 'aiLevel', 'isAi', 'game', 'pieces', 'color', 'isWinner', 'stack');
    }
}
