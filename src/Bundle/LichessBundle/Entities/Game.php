<?php

namespace Bundle\LichessBundle\Entities;

use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Chess\Clock;
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
    const DRAW = 34;

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
    protected $hash = '';

    /**
     * The game board
     *
     * @var Board
     */
    protected $board = null;

    /**
     * PGN moves of the game, separed by spaces
     *
     * @var string
     */
    protected $pgnMoves = null;

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

    /**
     * Array of position hashes, used to detect threefold repetition
     *
     * @var array
     */
    protected $positionHashes = array();

    /**
     * The game clock
     *
     * @var Clock
     */
    protected $clock = null;

    public function __construct()
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
        for ( $i = 0; $i < 6; $i++ ) {
            $this->hash .= $chars[mt_rand( 0, 63 )];
        }
        $this->status = self::CREATED;
        $this->room = new Room();
    }

    /**
     * Get clock
     * @return Clock
     */
    public function getClock()
    {
        return $this->clock;
    }

    /**
     * Set clock
     * @param  Clock
     * @return null
     */
    public function setCLock(Clock $clock)
    {
        $this->clock = $clock;
    }

    /**
     * Tell if the game has a clock
     *
     * @return boolean
     **/
    public function hasClock()
    {
        return null !== $this->clock;
    }

    /**
     * Add the current position hash to the stack
     */
    public function addPositionHash()
    {
        $hash = '';
        foreach($this->getPieces() as $piece) {
            $hash .= $piece->getContextualHash();
        }
        $this->positionHashes[] = md5($hash);
    }

    /**
     * Sometime we can safely clear the position hashes,
     * for example when a pawn moved
     *
     * @return void
     */
    public function clearPositionHashes()
    {
        $this->positionHashes = array();
    }

    /**
     * Are we in a threefold repetition state?
     *
     * @return bool
     **/
    public function isThreefoldRepetition()
    {
        $hash = end($this->positionHashes);

        return count(array_keys($this->positionHashes, $hash)) >= 3;
    }

    /**
     * Return true if the game can not be won anymore
     * and can be declared as draw automatically
     *
     * @return boolean
     **/
    public function isCandidateToAutoDraw()
    {
        if(1 === $this->getPlayer('white')->getNbAlivePieces() && 1 === $this->getPlayer('black')->getNbAlivePieces()) {
            return true;
        }

        return false;
    }

    /**
     * Get pgn moves
     * @return string
     */
    public function getPgnMoves()
    {
        return $this->pgnMoves;
    }

    /**
     * Set pgn moves
     * @param  string
     * @return null
     */
    public function setPgnMoves($pgnMoves)
    {
        $this->pgnMoves = $pgnMoves;
    }

    /**
     * Add a pgn move
     *
     * @param string
     * @return null
     **/
    public function addPgnMove($pgnMove)
    {
        if(null !== $this->pgnMoves) {
            $this->pgnMoves .= ' ';
        }
        $this->pgnMoves .= $pgnMove;
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
        case self::STALEMATE: $message = 'Stalemate'; break;
        case self::TIMEOUT: $message = ucfirst($this->getWinner()->getOpponent()->getColor()).' left the game'; break;
        case self::DRAW: $message = 'Draw'; break;
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

        if($this->status >= static::MATE && $this->hasClock()) {
            $this->getClock()->stop();
        }
    }

    /**
     * Start a game
     *
     * @return null
     **/
    public function start()
    {
        $this->setStatus(static::STARTED);
        if(!$this->getInvited()->getIsAi()) {
            $this->getRoom()->addMessage('system', ucfirst($this->getCreator()->getColor()).' creates the game');
            $this->getRoom()->addMessage('system', ucfirst($this->getInvited()->getColor()).' joins the game');
        }
        if($this->hasClock()) {
            $this->getClock()->start();
        }
    }

    /**
     * Get room
     * @return Room
     */
    public function getRoom()
    {
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
        elseif($this->getPlayer('black')->getHash() === $hash) {
            return $this->getPlayer('black');
        }
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

    public function getPersistentPropertyNames()
    {
        return array('hash', 'status', 'players', 'turns', 'creator', 'positionHashes');
    }

    public function serialize()
    {
        return $this->getPersistentPropertyNames();
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
