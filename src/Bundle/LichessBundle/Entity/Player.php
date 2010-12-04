<?php

namespace Bundle\LichessBundle\Entity;

use Bundle\LichessBundle\Model;
use Bundle\LichessBundle\Util\KeyGenerator;
use Bundle\LichessBundle\Chess\PieceFilter;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Bundle\DoctrineUserBundle\Model\User;

/**
 * Represents a single Chess player for one game
 *
 * @orm:Entity
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class Player implements Model\Player
{
    /**
     * Unique ID of the player for this game
     *
     * @var string
     * @orm:Column(type="string")
     */
    protected $id;

    /**
     * User bound to the player - optional
     *
     * @var User
     * @orm:OnetoOne(targetEntity="Application\DoctrineUserBundle\Document\User")
     */
    protected $user = null;

    /**
     * Fixed ELO of the player user, if any
     *
     * @var int
     * @orm:Column(type="integer")
     */
    protected $elo = null;

    /**
     * the player color, white or black
     *
     * @var string
     * @orm:Column(type="string")
     */
    protected $color;

    /**
     * Whether the player won the game or not
     *
     * @var boolean
     * @orm:Column(type="boolean")
     */
    protected $isWinner;

    /**
     * Whether this player is an Artificial intelligence or not
     *
     * @var boolean
     * @orm:Column(type="boolean")
     */
    protected $isAi;

    /**
     * If the player is an AI, its level represents the AI intelligence
     *
     * @var int
     * @orm:Column(type="integer")
     */
    protected $aiLevel;

    /**
     * Event stack
     *
     * @var Stack
     * @orm:OneToOne(targetEntity="Stack")
     */
    protected $stack;

    /**
     * the player pieces
     *
     * @var Collection
     * @orm:OneToMany(targetEntity="Piece")
     */
    protected $pieces;

    /**
     * Whether the player is offering draw or not
     *
     * @var bool
     * @orm:Column(type="boolean")
     */
    protected $isOfferingDraw = null;

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
        $this->pieces = new ArrayCollection();
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
    }

    public function canOfferDraw()
    {
        return $this->getGame()->getIsStarted()
            && !$this->getGame()->getIsFinished()
            && !$this->getIsOfferingDraw()
            && !$this->getOpponent()->getIsAi()
            && !$this->getOpponent()->getIsOfferingDraw();
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
        return $this->pieces->toArray();
    }

    /**
     * @param array
     */
    public function setPieces(array $pieces)
    {
        foreach($this->pieces as $index => $p) {
            $this->pieces->remove($index);
        }
        foreach($pieces as $piece) {
            $this->addPiece($piece);
        }
    }

    public function addPiece(Model\Piece $piece)
    {
        $this->pieces->add($piece);
        $piece->setPlayer($this);
    }

    public function removePiece(Model\Piece $piece)
    {
        $this->pieces->removeElement($piece);
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
    public function setGame(Model\Game $game)
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
}
