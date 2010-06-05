<?php

namespace Bundle\LichessBundle\Entities;

use Bundle\LichessBundle\Chess\Board;

/**
 * Represents a single Chess game
 *
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class Game
{
    /**
     * Whether the game has started or not
     *
     * @var boolean
     */
    protected $isStarted = false;

    /**
     * Whether the game is finished or not
     *
     * @var boolean
     */
    protected $isFinished = false;

    /**
     * The two players 
     * 
     * @var array
     */
    protected $players = array();

    /**
     * The player who created the game
     *
     * @var Player
     */
    protected $creator = null;

    /**
     * Number of turns passed
     *
     * @var integer
     */
    protected $turns = 0;

    /**
     * unique hash of the game
     *
     * @var string
     */
    protected $hash = null;

    /**
     * The game board
     *
     * @var Board
     */
    protected $board = null;

    /**
     * Update time in timestamp
     * Not persistent ; regenerated when game is loaded
     *
     * @var integer
     */
    protected $updatedAt = null;
    
    public function __construct()
    {
        $this->hash = '';
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789_';
        for ( $i = 0; $i < 6; $i++ ) {
            $this->hash .= $chars[mt_rand( 0, 36 )];
        }
    }
    
    /**
     * @return integer
     */
    public function getUpdatedAt()
    {
      return $this->updatedAt;
    }
    
    /**
     * @param integer
     */
    public function setUpdatedAt($updatedAt)
    {
      $this->updatedAt = $updatedAt;
    }

    /**
     * @return Board
     */
    public function getBoard()
    {
        if(null === $this->board) {
            $this->board = new Board($this);
        }
        return $this->board;
    }

    /**
     * @param Board
     */
    public function setBoard($board)
    {
        $this->board = $board;
    }

    /**
     * @return boolean
     */
    public function getIsFinished()
    {
        return $this->isFinished;
    }

    /**
     * @param boolean
     */
    public function setIsFinished($isFinished)
    {
        $this->isFinished = $isFinished;
    }

    /**
     * @return boolean
     */
    public function getIsStarted()
    {
        return $this->isStarted;
    }

    /**
     * @param boolean
     */
    public function setIsStarted($isStarted)
    {
        $this->isStarted = $isStarted;
    }

    public function setPlayers(array $players)
    {
        $this->players = $players;
    }

    public function getPlayers()
    {
        return $this->players;
    }

    /**
     * @return Player
     */
    public function getPlayer($color)
    {
        return $this->players[$color];
    }

    /**
     * @return Player
     */
    public function getPlayerByHash($hash)
    {
        if($this->getPlayer('white')->getHash() === $hash) {
            return $this->getPlayer('white');
        }

        return $this->getPlayer('black');
    }

    /**
     * @return Player
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @return Player
     */
    public function getInvited()
    {
        if(!$this->creator) {
            return null;
        }

        if($this->creator->isWhite()) {
            return $this->getPlayer('black');
        }

        return $this->getPlayer('white');
    }

    public function setCreator(Player $player)
    {
        $this->creator = $player;
    }

    public function getWinner()
    {
        if($this->getPlayer('white')->getIsWinner()) {
            return $this->getPlayer('white');
        }
        elseif($this->getPlayer('black')->getIsWinner()) {
            return $this->getPlayer('black');
        }
    }

    public function setPlayer($color, $player)
    {
        $this->players[$color] = $player;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return integer
     */
    public function getTurns()
    {
        return $this->turns;
    }

    /**
     * @param integer
     */
    public function setTurns($turns)
    {
        $this->turns = $turns;
    }

    public function getPieces()
    {
        return array_merge($this->getPlayer('white')->getPieces(), $this->getPlayer('black')->getPieces());
    }

    public function __toString()
    {
        return '#'.$this->getId(). 'turn '.$this->getTurns();
    }

    public function serialize()
    {
        return array('hash', 'isFinished', 'isStarted', 'players', 'turns', 'creator');
    }

    public function getClone()
    {
        $clone = clone($this);
        foreach($this->getPlayers() as $color => $player) {
            $clone->setPlayer($color, $player->getClone());
        }

        $clone->setBoard(clone $this->getBoard());
        $clone->getBoard()->compile();

        return $clone;
    }

    public function __clone()
    {
    }
}
