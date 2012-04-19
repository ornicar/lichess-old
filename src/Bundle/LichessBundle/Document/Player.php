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
    protected $c;

    /**
     * Whether the player won the game or not
     *
     * @var boolean
     * @MongoDB\Field(type="boolean")
     */
    protected $w;

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
     * the player stack events, compressed for efficient storage
     *
     * @var string
     * @MongoDB\String
     */
    protected $evts;

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
     * @var integer
     * @MongoDB\Field(type="int")
     */
    protected $blurs;

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
        $this->c = $color;
        $this->generateId();
    }

    public function getBlurs()
    {
        return $this->blurs ? $this->blurs : 0;
    }

    public function getBlurPercent()
    {
        $nbMoves = $this->getNbMoves();

        return $nbMoves == 0 ? 0 : round(($this->getBlurs() * 100) / $nbMoves);
    }

    public function getNbMoves()
    {
        return floor(($this->getGame()->getTurns() + ($this->isWhite() ? 1 : 0)) / 2);
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
    }

    /**
     * @return boolean
     */
    public function getIsWinner()
    {
        return (boolean) $this->w;
    }

    /**
     * @param boolean
     */
    public function setIsWinner($isWinner)
    {
        $this->w = $isWinner;
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
        $piece->setColor($this->getColor());
        $this->pieces[] = $piece;
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
        return $this->c;
    }

    public function getColorLetter()
    {
        return $this->c{0};
    }

    public function getOpponent()
    {
        return $this->getGame()->getPlayer('white' === $this->c ? 'black' : 'white');
    }

    public function isWhite()
    {
        return 'white' === $this->c;
    }

    public function isBlack()
    {
        return 'black' === $this->c;
    }

    public function __toString()
    {
        $string = $this->getColor().' '.($this->getIsAi() ? 'A.I.' : 'Human');

        return $string;
    }

    public function isMyTurn()
    {
        return $this->game->getTurns() %2 xor 'white' === $this->c;
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
            $ps[] = Board::keyToPiotr($piece->getSquareKey()) . $letter;
        }

        $this->ps = implode(' ', $ps);
    }

    public function extractPieces()
    {
        $pieces = array();
        if (!empty($this->ps)) {
            foreach(explode(' ', $this->ps) as $p) {
                $pos = Board::keyToPos(Board::piotrToKey($p{0}));
                $class = Piece::letterToClass(strtolower($p{1}));
                $piece = new Piece($pos[0], $pos[1], $class);
                if (ctype_upper($p{1})) $piece->setIsDead(true);
                $pieces[] = $piece;
            }
        }
        $this->setPieces($pieces);
    }
}
