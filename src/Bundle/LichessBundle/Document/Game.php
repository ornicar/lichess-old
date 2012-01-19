<?php

namespace Bundle\LichessBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Bundle\LichessBundle\Chess\Board;
use Bundle\LichessBundle\Util\KeyGenerator;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User;
use LogicException;

/**
 * Represents a single Chess game
 *
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 *
 * @MongoDB\Document(
 *   collection="game2",
 *   repositoryClass="Bundle\LichessBundle\Document\GameRepository"
 * )
 * @MongoDB\Index(keys={"userIds"="asc", "createdAt"="desc"})
 */
class Game
{
    const CREATED = 10;
    const STARTED = 20;
    const ABORTED = 25;
    const MATE = 30;
    const RESIGN = 31;
    const STALEMATE = 32;
    const TIMEOUT = 33;
    const DRAW = 34;
    const OUTOFTIME = 35;
    const CHEAT = 36;

    const VARIANT_STANDARD = 1;
    const VARIANT_960 = 2;

    /**
     * Unique ID of the game
     *
     * @var string
     * @MongoDB\Id(strategy="none")
     */
    protected $id;

    /**
     * Game variant (like standard or chess960)
     *
     * @var int
     * @MongoDB\Field(type="int")
     */
    protected $variant;

    /**
     * The current state of the game, like CREATED, STARTED or MATE.
     *
     * @var int
     * @MongoDB\Field(type="int")
     * @MongoDB\Index()
     */
    protected $status;

    /**
     * The two players
     *
     * @var array
     * @MongoDB\EmbedMany(targetDocument="Player")
     */
    protected $players;

    /**
     * Ids of the users bound to players
     *
     * @var array
     * @MongoDB\Field(type="collection")
     * @MongoDB\Index()
     */
    protected $userIds = array();

    /**
     * Denormalization
     * Id of the user who won the game
     * - string if the winner has a user
     * - false if the winner has no user
     * - null if there is no winner
     *
     * @var string
     * @MongoDB\Field(type="string")
     * @MongoDB\Index()
     */
    protected $winnerUserId = null;

    /**
     * @var integer
     * @MongoDB\Field(type="int")
     */
    protected $whiteBlurs;

    /**
     * @var integer
     * @MongoDB\Field(type="int")
     */
    protected $blackBlurs;

    /**
     * Color of the player who created the game
     *
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $creatorColor;

    /**
     * Number of turns passed
     *
     * @var integer
     * @MongoDB\Field(type="int")
     * @MongoDB\Index()
     */
    protected $turns = 0;

    /**
     * PGN moves of the game
     *
     * @var array
     * @MongoDB\Field(type="collection")
     */
    protected $pgnMoves = array();

    /**
     * Fen notation of the initial position
     * Can be null if equals to standard position
     *
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $initialFen;

    /**
     * Last update time
     *
     * @var \DateTime
     * @MongoDB\Field(type="date")
     * @MongoDB\Index(order="desc")
     */
    protected $updatedAt;

    /**
     * Creation date
     *
     * @var \DateTime
     * @MongoDB\Field(type="date")
     * @MongoDB\Index(order="desc")
     */
    protected $createdAt;

    /**
     * Array of position hashes, used to detect threefold repetition
     *
     * @var array
     * @MongoDB\Field(type="collection")
     */
    protected $positionHashes = array();

    /**
     * Internal notation of the last move played
     *
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $lastMove;

    /**
     * The game clock
     *
     * @var Clock
     * @MongoDB\EmbedOne(targetDocument="Clock", nullable=true)
     */
    protected $clock;

    /**
     * The chat room
     *
     * @var Room
     * @MongoDB\EmbedOne(targetDocument="Room", nullable=true)
     */
    protected $room;

    /**
     * Whether this game is rated or not
     *
     * @var bool
     * @MongoDB\Field(type="boolean")
     */
    protected $isRated;

    /**
     * If true, the elo points exchanged during this game have been canceled
     *
     * @var bool
     * @MongoDB\Field(type="boolean")
     */
    protected $isEloCanceled;

    /**
     * The previous game. This game is then a rematch of the previous game
     *
     * @var Game
     * @MongoDB\ReferenceOne(targetDocument="Game")
     */
    protected $previous;

    /**
     * The next game, if this game has been rematched
     *
     * @var Game
     * @MongoDB\ReferenceOne(targetDocument="Game")
     * @MongoDB\Index()
     */
    protected $next;

    /**
     * Config values used to create the game. Cleared when game starts.
     *
     * @var array
     * @MongoDB\Field(type="hash")
     */
    protected $configArray;

    /**
     * The game board
     *
     * @var Board
     */
    protected $board;

    public function __construct($variant = self::VARIANT_STANDARD)
    {
        $this->generateId();
        $this->setVariant($variant);
        $this->status   = self::CREATED;
        $this->players  = new ArrayCollection();
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
            throw new LogicException('Can not change the id of a saved game');
        }
        $this->id = KeyGenerator::generate(8);
    }

    /**
     * @return array
     */
    public function getConfigArray()
    {
        return $this->configArray;
    }

    /**
     * @param  array
     * @return null
     */
    public function setConfigArray(array $configArray = null)
    {
        $this->configArray = $configArray;
    }

    /**
     * @return string
     */
    public function getLastMove()
    {
        return $this->lastMove;
    }

    /**
     * @param  string
     * @return null
     */
    public function setLastMove($lastMove)
    {
        $this->lastMove = $lastMove;
    }

    /**
     * @return Game
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * @param  Game
     * @return null
     */
    public function setPrevious(Game $previous)
    {
        $this->previous = $previous;
    }

    /**
     * @return Game
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @param  Game
     * @return null
     */
    public function setNext(Game $next)
    {
        $this->next = $next;
    }

    /**
     * @return bool
     */
    public function getIsRated()
    {
        return (bool) $this->isRated;
    }

    /**
     * @param  bool
     * @return null
     */
    public function setIsRated($isRated)
    {
        if($this->getIsStarted()) {
            throw new LogicException('Can not change ranking mode, game is already started');
        }
        $this->isRated = $isRated ? true : null;
    }

    /**
     * @return bool
     */
    public function getIsEloCanceled()
    {
        return (bool) $this->isEloCanceled;
    }

    /**
     * @param  bool
     * @return null
     */
    public function setIsEloCanceled($isEloCanceled)
    {
        $this->isEloCanceled = $isEloCanceled ? true : null;
    }

    public function addUserId($userId)
    {
        if($userId && !in_array((string) $userId, $this->userIds)) {
            $this->userIds[] = (string) $userId;
        }
    }

    public function getUserIds()
    {
        return $this->userIds;
    }

    public function setWinner(Player $player)
    {
        $player->setIsWinner(true);
        $player->getOpponent()->setIsWinner(null);

        // Denormalization
        if($user = $player->getUser()) {
            $this->winnerUserId = (string) $user->getId();
        } else {
            $this->winnerUserId = false;
        }
    }

    public function incrementBlurs($color)
    {
        if ($this->getIsRated()) {
            if ('white' === $color) {
                ++ $this->whiteBlurs;
            } elseif ('black' === $color) {
                ++ $this->blackBlurs;
            }
        }
    }

    public function getBlurs()
    {
        return array(
            'white' => $this->whiteBlurs,
            'black' => $this->blackBlurs
        );
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
            throw new LogicException('Can not change variant, game is already started');
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
    public function setClock(Clock $clock = null)
    {
        if($this->getIsStarted()) {
            throw new LogicException('Can not add clock, game is already started');
        }

        $this->clock = $clock;
    }

    public function setClockTime($time, $increment)
    {
        $this->setClock($time ? new Clock($time, $increment) : null);
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

    public function getClockName()
    {
        return $this->hasClock() ? $this->getClock()->getName() : 'No clock';
    }

    public function estimateTotalTime()
    {
        return $this->hasClock() ? $this->getClock()->estimateTotalTime() : 1200; // defaults to 20 minutes
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
            throw new LogicException('This game has no clock');
        }
        if($this->getIsFinishedOrAborted()) {
            return;
        }
        foreach($this->getPlayers() as $player) {
            if($player->isMyTurn() && $this->getClock()->isOutOfTime($player->getColor())) {
                return $player;
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
        if(6 > count($this->positionHashes)) {
            return false;
        }
        $hash = end($this->positionHashes);

        return count(array_keys($this->positionHashes, $hash)) >= 3;
    }

    /**
     * Does the fifty moves autodraw rules apply now?
     *
     * @return bool
     **/
    public function isFiftyMoves()
    {
        // position hashes are half moves
        return 100 <= count($this->positionHashes);
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

    public function getHasEnoughMovesToDraw()
    {
        return $this->getTurns() >= 2;
    }

    /**
     * Tells if both players saved their move times
     *
     * @return boolean
     */
    public function hasMoveTimes()
    {
        return $this->getPlayer('white')->hasMoveTimes() && $this->getPlayer('black')->hasMoveTimes();
    }

    /**
     * Gets both player move times, by merging them sequentially
     *
     * @return array of int
     */
    public function getMoveTimes()
    {
        $times = array(0, 0);
        $playerTimes = array($this->getPlayer('white')->getMoveTimes(), $this->getPlayer('black')->getMoveTimes());
        foreach ($playerTimes[0] as $index => $whiteTime) {
            $times[] = $whiteTime;
            if (isset($playerTimes[1][$index])) {
                $times[] = $playerTimes[1][$index];
            }
        }

        return $times;
    }

    /**
     * Whether this game can be aborted or not
     *
     * @return bool
     **/
    public function getIsAbortable()
    {
        return self::STARTED === $this->getStatus() && 2 > $this->getTurns();
    }

    /**
     * Whether this game can be resigned or not
     *
     * @return bool
     **/
    public function isResignable()
    {
        return $this->getIsPlayable() && !$this->getIsAbortable();
    }

    /**
     * Get pgn moves
     * @return array
     */
    public function getPgnMoves()
    {
        return $this->pgnMoves;
    }

    /**
     * Set pgn moves
     * @param  array
     * @return null
     */
    public function setPgnMoves(array $pgnMoves)
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
        $this->pgnMoves[] = $pgnMove;
        $this->setUpdatedNow();
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
        case self::ABORTED: $message   = 'Game aborted'; break;
        case self::MATE: $message      = 'Checkmate'; break;
        case self::RESIGN: $message    = ucfirst($this->getWinner()->getOpponent()->getColor()).' resigned'; break;
        case self::STALEMATE: $message = 'Stalemate'; break;
        case self::TIMEOUT: $message   = ucfirst($this->getWinner()->getOpponent()->getColor()).' left the game'; break;
        case self::DRAW: $message      = 'Draw'; break;
        case self::OUTOFTIME: $message = 'Time out'; break;
        case self::CHEAT: $message     = 'Cheat detected'; break;
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
        if($this->getIsFinishedOrAborted()) {
            return;
        }

        $this->status = $status;

        if($this->getIsFinishedOrAborted() && $this->hasClock()) {
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
        // The game can only be rated if both players are logged in
        if($this->getIsRated() && !($this->getPlayer('white')->getUser() && $this->getPlayer('black')->getUser())) {
            $this->setIsRated(false);
        }
        $this->setStatus(static::STARTED);
        $this->addRoomMessage('system', ucfirst($this->getCreator()->getColor()).' creates the game');
        $this->addRoomMessage('system', ucfirst($this->getInvited()->getColor()).' joins the game');
        if($this->hasClock()) {
            $this->addRoomMessage('system', 'Clock: '.$this->getClock()->getName());
        }
        if($this->getIsRated()) {
            $this->addRoomMessage('system', 'This game is rated');
        }
        $this->setConfigArray(null);
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

    public function addRoomMessage($author, $message)
    {
        if($this->getInvited()->getIsAi()) {
            return false;
        }
        if(!$this->hasRoom()) {
            $this->setRoom(new Room());
        }
        $this->getRoom()->addMessage($author, $message);

        return true;
    }

    /**
     * @return Board
     */
    public function getBoard()
    {
        if(null === $this->board) {
            $this->ensureDependencies();
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
    public function getIsAborted()
    {
        return self::ABORTED === $this->getStatus();
    }

    public function getIsFinishedOrAborted()
    {
        return self::ABORTED <= $this->getStatus();
    }

    public function getIsPlayable()
    {
        return self::ABORTED > $this->getStatus();
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
        foreach($this->getPlayers() as $player) {
            if($color === $player->getColor()) {
                return $player;
            }
        }
    }

    /**
     * @return Player
     */
    public function getPlayerById($id)
    {
        foreach($this->getPlayers() as $player) {
            if($player->getId() === $id) {
                return $player;
            }
        }
    }

    public function getPlayerByUser(User $user = null)
    {
        if(null === $user) {
            return null;
        }
        foreach($this->getPlayers() as $p) {
            if($user->is($p->getUser())) {
                return $p;
            }
        }
    }

    public function getPlayerByUserOrCreator(User $user = null)
    {
        $player = $this->getPlayerByUser($user);
        if(empty($player)) {
            $player = $this->getCreator();
        }

        return $player;
    }

    public function hasUser()
    {
        foreach($this->getPlayers() as $p) {
            if($p->hasUser()) {
                return true;
            }
        }
        return false;
    }

    public function getVsText()
    {
        $creator = $this->getCreator();

        return sprintf('%s - %s', $creator->getUsernameWithElo(), $creator->getOpponent()->getUsernameWithElo());
    }

    /**
     * @return Player
     */
    public function getTurnPlayer()
    {
        return $this->getPlayer($this->getTurnColor());
    }

    /**
     * Add an event to both players stack
     *
     * @return null
     **/
    public function addEventToStacks(array $event)
    {
        foreach($this->getPlayers() as $player) {
            $player->addEventToStack($event);
        }
    }

    /**
     * Add many events to both players stack
     *
     * @return null
     **/
    public function addEventsToStacks(array $events)
    {
        foreach($this->getPlayers() as $player) {
            $player->addEventsToStack($events);
        }
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

    public function setCreator(Player $player)
    {
        $this->setCreatorColor($player->getColor());
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
        if($this->getCreator()->isWhite()) {
            return $this->getPlayer('black');
        } elseif($this->getCreator()->isBlack()) {
            return $this->getPlayer('white');
        }
    }

    public function getWinner()
    {
        foreach($this->getPlayers() as $player) {
            if($player->getIsWinner()) {
                return $player;
            }
        }
    }

    public function getLoser()
    {
        if($winner = $this->getWinner()) {
            return $winner->getOpponent();
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
        $pieces = array();
        foreach($this->getPlayers() as $player) {
            $pieces = array_merge($pieces, $player->getPieces());
        }

        return $pieces;
    }

    /**
     * Get updatedAt
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt ?: $this->getCreatedAt();
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
     * Tell if the game is being played right now
     * This method is not accurate
     *
     * @return bool
     **/
    public function isBeingPlayed()
    {
        if($this->getIsFinishedOrAborted()) {
            return false;
        }

        $interval = time() - $this->getUpdatedAt()->getTimestamp();

        return $interval < 20;
    }

    /**
     * Get createdAt
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function __toString()
    {
        return '#'.$this->getId(). 'turn '.$this->getTurns();
    }

    /**
     * @MongoDB\PrePersist
     */
    public function setCreatedNow()
    {
        $this->createdAt = new \DateTime();
    }

    public function setUpdatedNow()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @MongoDB\PostLoad
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
     * @MongoDB\PreUpdate
     * @MongoDB\PrePersist
     */
    public function cachePlayerVersions()
    {
        foreach($this->getPlayers() as $player) {
            if(!$player->getIsAi()) {
                apc_store($this->getId().'.'.$player->getColor().'.data', $player->getStack()->getVersion(), 3600);
            }
        }
    }
}
