<?php

namespace Bundle\LichessBundle\Document;

use Bundle\LichessBundle\Chess\Board;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Represents a single Chess game
 *
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 *
 * @mongodb:Document(
 *   collection="game2",
 *   repositoryClass="Bundle\LichessBundle\Document\GameRepository"
 * )
 * @mongodb:HasLifecycleCallbacks
 */
class Game
{
    const CREATED = 10;
    const STARTED = 20;
    const MATE = 30;
    const RESIGN = 31;
    const STALEMATE = 32;
    const TIMEOUT = 33;
    const DRAW = 34;
    const OUTOFTIME = 35;

    const VARIANT_STANDARD = 1;
    const VARIANT_960 = 2;

    /**
     * Unique ID of the game
     *
     * @var string
     * @mongodb:Id(custom="true")
     */
    protected $id = null;

    /**
     * Game variant (like standard or 960)
     *
     * @var int
     * @mongodb:Field(type="int", name="v")
     */
    protected $variant = self::VARIANT_STANDARD;

    /**
     * The current state of the game, like CREATED, STARTED or MATE.
     *
     * @var int
     * @mongodb:Field(type="int", name="s")
     * @mongodb:Index()
     */
    protected $status = self::CREATED;

    /**
     * The two players
     *
     * @var array
     * @mongodb:EmbedMany(targetDocument="Player", name="p")
     */
    protected $players = null;

    /**
     * Color of the player who created the game
     *
     * @var string
     * @mongodb:Field(type="string", name="cc")
     */
    protected $creatorColor = null;

    /**
     * Number of turns passed
     *
     * @var integer
     * @mongodb:Field(type="int", name="t")
     */
    protected $turns = 0;

    /**
     * PGN moves of the game, separed by spaces
     *
     * @var string
     * @mongodb:Field(type="string", name="pgn")
     */
    protected $pgnMoves = null;

    /**
     * The ID of the next game the players will start
     *
     * @var string
     * @mongodb:Field(type="string")
     */
    protected $next = null;

    /**
     * Fen notation of the initial position
     * Can be null if equals to standard position
     *
     * @var string
     * @mongodb:Field(type="string", name="ifen")
     */
    protected $initialFen = null;

    /**
     * Last update time
     *
     * @var \DateTime
     * @mongodb:Field(type="date", name="ua")
     * @mongodb:Index(order="desc")
     */
    protected $updatedAt = null;

    /**
     * Creation date
     *
     * @var \DateTime
     * @mongodb:Field(type="date", name="ca")
     */
    protected $createdAt = null;

    /**
     * Array of position hashes, used to detect threefold repetition
     *
     * @var array
     * @mongodb:Field(type="collection", name="ph")
     */
    protected $positionHashes = array();

    /**
     * The game clock
     *
     * @var Clock
     * @mongodb:EmbedOne(targetDocument="Clock", nullable=true, name="c")
     */
    protected $clock = null;

    /**
     * The chat room
     *
     * @var Room
     * @mongodb:EmbedOne(targetDocument="Room", nullable=true, name="r")
     */
    protected $room = null;

    /**
     * The game board
     *
     * @var Board
     */
    protected $board = null;

    public function __construct($variant = self::VARIANT_STANDARD)
    {
        $this->generateId();
        $this->setVariant($variant);
        $this->status = self::CREATED;
        $this->players = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Generate a new ID - don't use once the game is saved
     *
     * @return null
     **/
    protected function generateId()
    {
        if(null !== $this->id) {
            throw new \LogicException('Can not change the id of a saved game');
        }
        $this->id = '';
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789_-';
        $nbChars = strlen($chars);
        for ( $i = 0; $i < 8; $i++ ) {
            $this->id .= $chars[mt_rand(0, $nbChars-1)];
        }
    }

    /**
     * Fen notation of initial position
     *
     * @return string
     **/
    public function getInitialFen()
    {
        if(null === $this->initialFen) {
            return 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq';
        }

        return $this->initialFen;
    }

    /**
     * Set initialFen
     * @param  string
     * @return null
     */
    public function setInitialFen($fen)
    {
        $this->initialFen = $fen;
    }

    /**
     * Get variant
     * @return int
     */
    public function getVariant()
    {
        return $this->variant;
    }

    /**
     * Set variant
     * @param  int
     * @return null
     */
    public function setVariant($variant)
    {
        if(!array_key_exists($variant, self::getVariantNames())) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid game variant', $variant));
        }
        if($this->getIsStarted()) {
            throw new \LogicException('Can not change variant, game is already started');
        }
        $this->variant = $variant;
    }

    public function isStandardVariant()
    {
        return static::VARIANT_STANDARD === $this->variant;
    }

    public function getVariantName()
    {
        $variants = self::getVariantNames();

        return $variants[$this->getVariant()];
    }

    static public function getVariantNames()
    {
        return array(
            self::VARIANT_STANDARD => 'standard',
            self::VARIANT_960 => 'chess960'
        );
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
    public function setClock(Clock $clock)
    {
        if($this->getIsStarted()) {
            throw new \LogicException('Can not add clock, game is already started');
        }
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
     * Get the minutes of the clock if any, or 0
     *
     * @return int
     **/
    public function getClockMinutes()
    {
        return $this->hasClock() ? $this->getClock()->getLimitInMinutes() : 0;
    }

    /**
     * Verify if one of the player exceeded his time limit,
     * and terminate the game in this case
     *
     * @return boolean true if the game has been terminated
     **/
    public function checkOutOfTime()
    {
        if(!$this->hasClock()) {
            throw new \LogicException('This game has no clock');
        }
        if($this->getIsFinished()) {
            return;
        }
        foreach($this->getPlayers() as $player) {
            if($this->getClock()->isOutOfTime($player->getColor())) {
                $this->setStatus(static::OUTOFTIME);
                $player->getOpponent()->setIsWinner(true);
                return true;
            }
        }
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
     * Halfmove clock: This is the number of halfmoves since the last pawn advance or capture.
     * This is used to determine if a draw can be claimed under the fifty-move rule.
     *
     * @return int
     **/
    public function getHalfmoveClock()
    {
        return max(0, count($this->positionHashes) - 1);
    }

    /**
     * Fullmove number: The number of the full move. It starts at 1, and is incremented after Black's move.
     *
     * @return int
     **/
    public function getFullmoveNumber()
    {
        return floor(1+$this->getTurns() / 2);
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
        case self::MATE: $message      = 'Checkmate'; break;
        case self::RESIGN: $message    = ucfirst($this->getWinner()->getOpponent()->getColor()).' resigned'; break;
        case self::STALEMATE: $message = 'Stalemate'; break;
        case self::TIMEOUT: $message   = ucfirst($this->getWinner()->getOpponent()->getColor()).' left the game'; break;
        case self::DRAW: $message      = 'Draw'; break;
        case self::OUTOFTIME: $message = 'Time out'; break;
        default: $message              = '';
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
        if($this->getIsFinished()) {
            return;
        }

        $this->status = $status;

        if($this->getIsFinished() && $this->hasClock()) {
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
            if(!$this->hasRoom()) {
                $this->setRoom(new Room());
            }
            $this->getRoom()->addMessage('system', ucfirst($this->getCreator()->getColor()).' creates the game');
            $this->getRoom()->addMessage('system', ucfirst($this->getInvited()->getColor()).' joins the game');
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

    public function hasRoom()
    {
        return null !== $this->room;
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

    /**
     * @return Collection
     */
    public function getPlayers()
    {
        return $this->players;
    }

    /**
     * @return Player
     */
    public function getPlayer($color)
    {
        if($color === $this->players->get(0)->getColor()) {
            return $this->players->get(0);
        }
        elseif($color === $this->players->get(1)->getColor()) {
            return $this->players->get(1);
        }
    }

    /**
     * @return Player
     */
    public function getPlayerById($id)
    {
        if($this->getPlayer('white')->getId() === $id) {
            return $this->getPlayer('white');
        }
        elseif($this->getPlayer('black')->getId() === $id) {
            return $this->getPlayer('black');
        }
    }

    /**
     * @return Player
     */
    public function getTurnPlayer()
    {
        return $this->getPlayer($this->getTurnColor());
    }

    /**
     * Color who plays
     *
     * @return string
     **/
    public function getTurnColor()
    {
        return $this->turns%2 ? 'black' : 'white';
    }

    /**
     * @return string
     */
    public function getCreatorColor()
    {
      return $this->creatorColor;
    }

    /**
     * @param  string
     * @return null
     */
    public function setCreatorColor($creatorColor)
    {
      $this->creatorColor = $creatorColor;
    }

    /**
     * @return Player
     */
    public function getCreator()
    {
        return $this->getPlayer($this->getCreatorColor());
    }

    /**
     * @return Player
     */
    public function getInvited()
    {
        if(!$this->creatorColor) {
            return null;
        }

        if($this->getCreator()->isWhite()) {
            return $this->getPlayer('black');
        }

        return $this->getPlayer('white');
    }

    public function setCreator(Player $player)
    {
        $this->setCreatorColor($player->getColor());
    }

    public function getWinner()
    {
        foreach($this->getPlayers() as $player) {
            if($player->getIsWinner()) {
                return $player;
            }
        }
    }

    public function addPlayer(Player $player)
    {
        $this->players->add($player);
        $player->setGame($this);
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

    /**
     * Get updatedAt
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
      return $this->updatedAt;
    }

    /**
     * Set updatedAt
     * @param  \DateTime
     * @return null
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
      $this->updatedAt = $updatedAt;
    }

    /**
     * Get createdAt
     * @return \DateTime
     */
    public function getCreatedAt()
    {
      return $this->createdAt;
    }

    /**
     * Set createdAt
     * @param  \DateTime
     * @return null
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
      $this->createdAt = $createdAt;
    }

    public function __toString()
    {
        return '#'.$this->getId(). 'turn '.$this->getTurns();
    }

    /**
     * @mongodb:PrePersist
     */
    public function setCreatedNow()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @mongodb:PreUpdate
     */
    public function setUpdatedNow()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @mongodb:PostLoad
     */
    public function ensureDependencies()
    {
        $this->board = new Board($this);

        foreach($this->getPlayers() as $player) {
            $player->setGame($this);
            foreach($player->getPieces() as $piece) {
                $piece->setPlayer($player);
                $piece->setBoard($this->board);
            }
        }
    }

    /**
     * @mongodb:PreUpdate
     */
    public function rotatePlayerStacks()
    {
        foreach($game->getPlayers() as $player) {
            if(!$player->getIsAi()) {
                $player->getStack()->rotate();
            }
        }
    }

    /**
     * @mongodb:PreUpdate
     * @mongodb:PrePersist
     */
    public function cachePlayerVersions()
    {
        foreach($this->getPlayers() as $player) {
            if(!$player->getIsAi()) {
                apc_store($this->getId().'.'.$this->getColor().'.data', $player->getStack()->getVersion(), 3600);
            }
        }
    }
}
