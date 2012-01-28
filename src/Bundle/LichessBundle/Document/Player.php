<?php

namespace Bundle\LichessBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Bundle\LichessBundle\Util\KeyGenerator;
use Bundle\LichessBundle\Chess\PieceFilter;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User;
use Bundle\LichessBundle\Chess\Board;

/**
 * Represents a single Chess player for one game
 *
 * @MongoDB\EmbeddedDocument
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class Player
{
    /**
     * Unique ID of the player for this game
     *
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $id;

    /**
     * User bound to the player - optional
     *
     * @var User
     * @MongoDB\ReferenceOne(targetDocument="Application\UserBundle\Document\User")
     */
    protected $user = null;

    /**
     * Fixed ELO of the player user, if any
     *
     * @var int
     * @MongoDB\Field(type="int")
     */
    protected $elo = null;

    /**
     * Elo the players gains or loses during this game
     *
     * @var int
     * @MongoDB\Field(type="int")
     */
    protected $eloDiff = null;

    /**
     * the player color, white or black
     *
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $color;

    /**
     * Whether the player won the game or not
     *
     * @var boolean
     * @MongoDB\Field(type="boolean")
     */
    protected $isWinner;

    /**
     * Whether this player is an Artificial intelligence or not
     *
     * @var boolean
     * @MongoDB\Field(type="boolean")
     */
    protected $isAi;

    /**
     * If the player is an AI, its level represents the AI intelligence
     *
     * @var int
     * @MongoDB\Field(type="int")
     */
    protected $aiLevel;

    /**
     * Event stack
     *
     * @var Stack
     * @MongoDB\EmbedOne(targetDocument="Stack")
     */
    protected $stack;

    /**
     * the player pieces, extracted from ps
     *
     * @var array
     */
    protected $pieces;

    /**
     * the player pieces, compressed for efficient storage
     *
     * @var string
     * @MongoDB\String
     */
    protected $ps;

    /**
     * Whether the player is offering draw or not
     *
     * @var bool
     * @MongoDB\Field(type="boolean")
     */
    protected $isOfferingDraw = null;

    /**
     * Whether the player is offering rematch or not
     *
     * @var bool
     * @MongoDB\Field(type="boolean")
     */
    protected $isOfferingRematch = null;

    /**
     * Number of turns when last offered a draw
     *
     * @var int
     * @MongoDB\Field(type="int")
     */
    protected $lastDrawOffer = null;

    /**
     * Array of move times relative to the opponent previous move
     * Which really means: the time used to make the move
     *
     * @var array of int compressed to string
     * @MongoDB\String
     */
    protected $mts = null;

    /**
     * Previous move timestamp
     *
     * @var int
     * @MongoDB\Field(type="int")
     */
    protected $previousMoveTs = null;

    /**
     * the player current game
     *
     * @var Game
     */
    protected $game;

    public function __construct($color)
    {
        if(!in_array($color, array('white', 'black'))) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid player color'));
        }
        $this->color = $color;
        $this->generateId();
        $this->stack = new Stack();
        $this->addEventToStack(array('type' => 'start'));
    }

    public function finish()
    {
        if(!empty($this->previousMoveTs)) {
            $this->previousMoveTs = null;
        }
    }

    /**
     * Adds a time to the move times list
     */
    public function saveMoveTime()
    {
        $ts = time();
        if ($opmt = $this->getOpponent()->getPreviousMoveTs()) {
            $mt = $ts - $opmt;
            if (empty($this->mts)) $this->mts = (string) $mt;
            else $this->mts .= ' ' . $mt;
        }
        $this->previousMoveTs = $ts;
    }

    /**
     * Tells if this player saved his move times
     *
     * @return boolean
     */
    public function hasMoveTimes()
    {
        return !empty($this->mts) && strlen($this->mts) > 12;
    }

    /**
     * Gets the moves times
     *
     * @return array of int
     */
    public function getMoveTimes()
    {
        return array_map(function($t) { return (int)$t; }, explode(' ', $this->mts));
    }

    /**
     * Gets the timestamp of the previous move played
     *
     * @return int
     */
    public function getPreviousMoveTs()
    {
        return $this->previousMoveTs;
    }

    /**
     * @return int
     */
    public function getEloDiff()
    {
        return $this->eloDiff;
    }

    /**
     * @param  int
     * @return null
     */
    public function setEloDiff($eloDiff)
    {
        $this->eloDiff = $eloDiff;
    }

    /**
     * @return bool
     */
    public function getIsOfferingDraw()
    {
        return $this->isOfferingDraw;
    }

    /**
     * @param  bool
     * @return null
     */
    public function setIsOfferingDraw($isOfferingDraw)
    {
        $this->isOfferingDraw = $isOfferingDraw ?: null;

        if($this->isOfferingDraw) {
            $this->lastDrawOffer = $this->getGame()->getTurns();
        }
    }

    /**
     * @return bool
     */
    public function getIsOfferingRematch()
    {
        return $this->isOfferingRematch;
    }

    /**
     * @param  bool
     * @return null
     */
    public function setIsOfferingRematch($isOfferingRematch)
    {
        $this->isOfferingRematch = $isOfferingRematch ?: null;
    }

    public function canOfferDraw()
    {
        return $this->getGame()->getIsStarted()
            && $this->getGame()->getIsPlayable()
            && $this->getGame()->getHasEnoughMovesToDraw()
            && !$this->getIsOfferingDraw()
            && !$this->getOpponent()->getIsAi()
            && !$this->getOpponent()->getIsOfferingDraw()
            && !$this->hasOfferedDraw();
    }

    protected function hasOfferedDraw()
    {
        if(!$this->lastDrawOffer) {
            return false;
        }

        return $this->lastDrawOffer >= ($this->getGame()->getTurns() - 1);
    }

    public function canOfferRematch()
    {
        return $this->getGame()->getIsFinishedOrAborted()
            && !$this->getOpponent()->getIsAi();
    }

    /**
     * Get the user bound to this player, if any
     *
     * @return User or null
     */
    public function getUser()
    {
        return $this->user;
    }

    public function hasUser()
    {
        return $this->user != null;
    }

    /**
     * Set the user bound to this player
     *
     * @param User $user
     * @return null
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
        if($this->user) {
            $this->elo = $user->getElo();
            $this->getGame()->addUserId($user->getId());
        }
    }

    /**
     * @return int
     */
    public function getElo()
    {
        return $this->elo;
    }

    /**
     * Get the username of the player, or "Anonymous" if the player is not authenticated
     *
     * @return string
     **/
    public function getUsername($default = 'Anonymous')
    {
        if($this->getIsAi()) {
            return sprintf('A.I. level %d', $this->getAiLevel());
        }
        $user = $this->getUser();
        if(!$user) {
            return $default;
        }

        return $user->getUsername();
    }

    /**
     * Get the username and ELO of the player, or "Anonymous" if the player is not authenticated
     *
     * @return string
     **/
    public function getUsernameWithElo($default = 'Anonymous')
    {
        if($this->getIsAi()) {
            return sprintf('A.I. level %d', $this->getAiLevel());
        }
        $user = $this->getUser();
        if(!$user) {
            return $default;
        }

        return sprintf('%s (%d)', $user->getUsername(), $this->getElo());
    }

    /**
     * Generate a new ID - don't use once the player is saved
     *
     * @return null
     **/
    protected function generateId()
    {
        if(null !== $this->id) {
            throw new \LogicException('Can not change the id of a saved player');
        }
        $this->id = KeyGenerator::generate(4);
    }

    /**
     * Get stack
     * @return Stack
     */
    public function getStack()
    {
        if (null === $this->stack) $this->stack = new Stack();

        return $this->stack;
    }

    public function addEventsToStack(array $events)
    {
        if(!$this->getIsAi()) {
            $this->getStack()->addEvents($events);
        }
    }

    public function addEventToStack(array $event)
    {
        if(!$this->getIsAi()) {
            $this->getStack()->addEvent($event);
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFullId()
    {
        return $this->game->getId().$this->getId();
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
        foreach($this->getPieces() as $piece) {
            if($piece instanceof Piece\King) {
                return $piece;
            }
        }
    }

    /**
     * @return array
     */
    public function getPiecesByClass($class) {
        $pieces = array();
        foreach($this->getPieces() as $piece) {
            if($piece->isClass($class)) {
                $pieces[] = $piece;
            }
        }
        return $pieces;
    }

    public function getNbAlivePieces()
    {
        $nb = 0;
        foreach($this->getPieces() as $piece) {
            if(!$piece->getIsDead()) {
                ++$nb;
            }
        }

        return $nb;
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
        return (boolean) $this->isAi;
    }

    /**
     * @return boolean
     */
    public function getIsHuman()
    {
        return !$this->getIsAi();
    }

    /**
     * @param boolean
     */
    public function setIsAi($isAi)
    {
        $this->isAi = $isAi;

        if($this->isAi) {
            $this->getStack()->reset();
        }
    }

    /**
     * @return boolean
     */
    public function getIsWinner()
    {
        return (boolean) $this->isWinner;
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
    public function setPieces(array $pieces)
    {
        $this->pieces = array();
        foreach($pieces as $piece) {
            $this->addPiece($piece);
        }
    }

    public function addPiece(Piece $piece)
    {
        $piece->setPlayer($this);
        $this->pieces[] = $piece;
    }

    public function removePiece(Piece $piece)
    {
        foreach ($this->pieces as $i => $p) {
            if ($p === $piece) {
                unset($this->pieces[$i]);
                break;
            }
        }
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
    public function setGame(Game $game)
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

    public function getOpponent()
    {
        return $this->getGame()->getPlayer('white' === $this->color ? 'black' : 'white');
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
        return $this->game->getTurns() %2 xor 'white' === $this->color;
    }

    public function getBoard()
    {
        return $this->getGame()->getBoard();
    }

    public function compressPieces()
    {
        $ps = array();
        foreach($this->getPieces() as $piece) {
            $letter = Piece::classToLetter($piece->getClass());
            if ($piece->getIsDead()) $letter = strtoupper($letter);
            $ps[] = Board::keyToPiotr($piece->getSquareKey()) . $letter . $piece->getFirstMove();
        }

        $this->ps = implode(' ', $ps);
    }

    public function extractPieces()
    {
        $pieces = array();
        foreach(explode(' ', $this->ps) as $p) {
            $class = 'Bundle\\LichessBundle\\Document\\Piece\\'.Piece::letterToClass(strtolower($p{1}));
            $pos = Board::keyToPos(Board::piotrToKey($p{0}));
            $piece = new $class($pos[0], $pos[1]);
            if (ctype_upper($p{1})) $piece->setIsDead(true);
            if ($meta = substr($p, 2)) $piece->setFirstMove((int)$meta);
            $pieces[] = $piece;
        }
        $this->setPieces($pieces);
    }
}
