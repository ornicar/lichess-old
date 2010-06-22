<?php

namespace Bundle\LichessBundle\Entities;

use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Entities\Chat\Room;

/**
 * Represents a single Chess game
 *
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class Game
{
    /**
     * The current state of the game, like CREATED, STARTED or MATE.
     *
     * @var int
     */
    protected $status = self::CREATED;
    
    const CREATED = 10;
    const STARTED = 20;
    const MATE = 30;
    const RESIGN = 31;
    const STALEMATE = 32;
    const TIMEOUT = 33;

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

    /**
     * The chat room
     *
     * @var Room
     */
    protected $room = null;

    /**
     * The hash code of the next game the players will start
     *
     * @var string
     */
    protected $next = null;
    
    public function __construct()
    {
        $this->hash = '';
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789_';
        for ( $i = 0; $i < 6; $i++ ) {
            $this->hash .= $chars[mt_rand( 0, 36 )];
        }
        $this->status = self::CREATED;
    }
    
    /**
     * Get next
     * @return string
     */
    public function getNext()
    {
      return $this->next;
    }
    
    /**
     * Set next
     * @param  string
     * @return null
     */
    public function setNext($next)
    {
      $this->next = $next;
    }
    
    /**
     * Get status
     * @return int
     */
    public function getStatus()
    {
      return $this->status;
    }

    public function getStatusMessage()
    {
        switch($this->getStatus()) {
            case self::MATE: $message = 'Checkmate'; break;
            case self::RESIGN: $message = ucfirst($this->getWinner()->getOpponent()->getColor()).' resigned'; break;
            case self::STALEMATE: $message = ''; break;
            case self::TIMEOUT: $message = ucfirst($this->getWinner()->getOpponent()->getColor()).' left the game'; break;
            default: $message = '';
        }
        return $message;
    }
    
    /**
     * Set status
     * @param  int
     * @return null
     */
    public function setStatus($status)
    {
      $this->status = $status;
    }

    /**
     * Get room
     * @return Room
     */
    public function getRoom()
    {
        if(null === $this->room) {
            $this->room = new Room();
        }
        return $this->room;
    }

    /**
     * Set room
     * @param  Room
     * @return null
     */
    public function setRoom($room)
    {
        $this->room = $room;
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
        return $this->getStatus() >= self::MATE;
    }

    /**
     * @return boolean
     */
    public function getIsStarted()
    {
        return $this->getStatus() >= self::STARTED;
    }

    /**
     * @return boolean
     */
    public function getIsTimeOut()
    {
        return $this->getStatus() === self::TIMEOUT;
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
    public function getTurnPlayer()
    {
        return $this->turns%2 ? $this->getPlayer('black') : $this->getPlayer('white');
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

    public function addTurn()
    {
        ++$this->turns;
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
        return array('hash', 'status', 'players', 'turns', 'creator');
    }

    public function unserialize()
    {
        $board = $this->getBoard();
        foreach($this->getPlayers() as $player) {
            foreach ($player->getPieces() as $piece) {
                $piece->setBoard($board);
            }
        }
    }
}
