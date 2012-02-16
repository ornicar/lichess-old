<?php

namespace Lichess\OpeningBundle\Document;

use Application\UserBundle\Document\User;
use Bundle\LichessBundle\Document\Game;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Bundle\LichessBundle\Util\KeyGenerator;
use Lichess\OpeningBundle\Config\GameConfigView;

/**
 * Invitation to play. Contains game configuration.
 *
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 *
 * @MongoDB\Document(
 *   collection="hook",
 *   repositoryClass="Lichess\OpeningBundle\Document\HookRepository"
 * )
 */
class Hook
{
    /**
     * Public unique identifier of the hook
     *
     * @var string
     * @MongoDB\Id(strategy="none")
     */
    protected $id = null;

    /**
     * Private unique identifier of the hook
     *
     * @var string
     * @MongoDB\Field(type="string")
     * @MongoDB\UniqueIndex()
     */
    protected $ownerId = null;

    /**
     * Game variant (like standard or chess960)
     *
     * @var int
     * @MongoDB\Field(type="int")
     */
    protected $variant = null;

    /**
     * Whether or not a clock is used
     *
     * @var boolean
     * @MongoDB\Field(type="boolean")
     */
    protected $hasClock = false;

    /**
     * Maximum time of the clock per player, in minutes
     *
     * @var int
     * @MongoDB\Field(type="int")
     */
    protected $time = null;

    /**
     * Fisher clock bonus per move in seconds
     *
     * @var int
     * @MongoDB\Field(type="int")
     */
    protected $increment = null;

    /**
     * 0=casual, 1=rated
     *
     * @var int
     * @MongoDB\Field(type="int")
     * @MongoDB\Index()
     */
    protected $mode = null;

    /**
     * Creator player color
     *
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $color = null;

    /**
     * Optional registered user who owns the hook
     *
     * @var User
     * @MongoDB\ReferenceOne(targetDocument="Application\UserBundle\Document\User")
     */
    protected $user = null;

    /**
     * Denormalization of the user name
     *
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $username = 'Anonymous';

    /**
     * Denormalization of the user elo
     *
     * @var int
     * @MongoDB\Field(type="int")
     */
    protected $elo;

    /**
     * When the hook was created
     *
     * @var DateTime
     * @MongoDB\Field(type="date")
     * @MongoDB\Index(order="desc")
     */
    protected $createdAt = null;

    /**
     * Game created when a fish bites the hook
     *
     * @var Game
     * @MongoDB\ReferenceOne(targetDocument="Bundle\LichessBundle\Document\Game")
     */
    protected $game = null;

    /**
     * True when someone bite to the hook
     *
     * @var boolean
     * @MongoDB\Field(type="boolean")
     * @MongoDB\Index()
     */
    protected $match = false;

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $eloRange = null;

    /**
     * Whether the creator uses an engine
     *
     * @MongoDB\Field(type="boolean")
     * @var bool
     */
    protected $engine = false;

    public function __construct()
    {
        $this->id      = KeyGenerator::generate(8);
        $this->ownerId = $this->id.KeyGenerator::generate(4);
        $this->createdAt = new \DateTime();
    }

    public function fromArray(array $data)
    {
        if(isset($data['clock'])) $this->hasClock = (boolean) $data['clock'];
        if(isset($data['time'])) $this->time = $data['time'];
        if(isset($data['increment'])) $this->increment = $data['increment'];
        if(isset($data['variant'])) $this->variant = $data['variant'];
        if(isset($data['mode'])) $this->mode = $data['mode'];
        if(isset($data['color'])) $this->color = $data['color'];
        if(isset($data['eloRange'])) $this->eloRange = $data['eloRange'];
    }

    public function toArray()
    {
        return array('clock' => $this->hasClock, 'time' => $this->time, 'increment' => $this->increment, 'variant' => $this->variant, 'mode' => $this->mode, 'color' => $this->color, 'eloRange' => $this->getEloRange());
    }

    public function isEngine()
    {
        return $this->engine;
    }

    public function userCanJoin($user = null)
    {
        if (!$user instanceof User) $user = null;

        if ($this->isRated()) {
            if (!$user) return false;
            if ($this->getEloRange()) {
                return $user->getElo() >= $this->getEloMin() && $user->getElo() <= $this->getEloMax();
            }
        }

        return true;
    }

    /**
     * Gets: Game variant (like standard or chess960)
     *
     * @return int variant
     */
    public function getVariant()
    {
        return $this->variant;
    }

    /**
     * Sets: Game variant (like standard or chess960)
     *
     * @param int variant
     */
    public function setVariant($variant)
    {
        $this->variant = $variant;
    }

    /**
     * @return boolean
     */
    public function getHasClock()
    {
        return $this->hasClock;
    }

    /**
     * @param  boolean
     * @return null
     */
    public function setHasClock($hasClock)
    {
        $this->hasClock = $hasClock;
    }

    /**
     * Gets: Maximum time of the clock per player, in minutes
     *
     * @return int time
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Sets: Maximum time of the clock per player, in minutes
     *
     * @param int time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * Gets: Fisher clock bonus per move in seconds
     *
     * @return int increment
     */
    public function getIncrement()
    {
        return $this->increment;
    }

    /**
     * Sets: Fisher clock bonus per move in seconds
     *
     * @param int increment
     */
    public function setIncrement($increment)
    {
        $this->increment = $increment;
    }

    /**
     * Gets: 0=casual, 1=rated
     *
     * @return int mode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Sets: 0=casual, 1=rated
     *
     * @param int mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    public function isRated()
    {
        return $this->mode === 1;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param  string
     * @return null
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getEloRange()
    {
        if ($this->isRated()) {
            return $this->eloRange;
        }
    }

    /**
     * @param  string
     * @return null
     */
    public function setEloRange($eloRange)
    {
        $this->eloRange = $eloRange;
    }

    public function getEloMin()
    {
        if (empty($this->eloRange)) return null;

        return substr($this->eloRange, 0, strpos($this->eloRange, '-'));
    }

    public function getEloMax()
    {
        if (empty($this->eloRange)) return null;

        return substr($this->eloRange, strpos($this->eloRange, '-') + 1);
    }

    /**
     * Gets: Optional registered user who owns the hook
     *
     * @return User user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets: Optional registered user who owns the hook
     *
     * @param User user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        $this->username = $user->getUsername();
        $this->elo = $user->getElo();
        $this->engine = $user->isEngine();
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return int
     */
    public function getElo()
    {
        return $this->elo;
    }

    /**
     * Gets: When the hook was created
     *
     * @return DateTime createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Gets: Public unique identifier of the hook
     *
     * @return string id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets: Private unique identifier of the hook
     *
     * @return string ownerId
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * Gets: Game created when a fish bites the hook
     *
     * @return Game game
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * Sets: Game created when a fish bites the hook
     *
     * @param Game game
     */
    public function setGame(Game $game)
    {
        $this->game = $game;
        $this->match = true;
    }

    public function isMatch()
    {
        return $this->match;
    }
}
