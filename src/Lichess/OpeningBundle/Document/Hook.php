<?php

namespace Lichess\HookBundle\Document;

use Application\UserBundle\Document\User;
use Bundle\LichessBundle\Document\Game;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Bundle\LichessBundle\Util\KeyGenerator;

/**
 * Invitation to play. Contains game configuration.
 *
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 *
 * @MongoDB\Document(
 *   collection="hook",
 *   repositoryClass="Lichess\HookBundle\Document\HookRepository"
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
     */
    protected $mode = null;

    /**
     * Optional registered user who owns the hook
     *
     * @var User
     * @MongoDB\ReferenceOne(targetDocument="Application\UserBundle\Document\User")
     */
    protected $user = null;

    /**
     * When the hook was created
     *
     * @var DateTime
     * @MongoDB\Field(type="date")
     * @MongoDB\Index(order="desc")
     */
    protected $createdAt = null;

    /**
     * When the hook was deleted
     *
     * @var DateTime
     * @MongoDB\Field(type="date")
     * @MongoDB\Index(order="desc")
     */
    protected $deletedAt = null;

    /**
     * Game created when a fish bites the hook
     *
     * @var Game
     * @MongoDB\ReferenceOne(targetDocument="Bundle\LichessBundle\Document\Game")
     */
    protected $game = null;

    public function __construct()
    {
        $this->id      = KeyGenerator::generate(8);
        $this->ownerId = $this->id.KeyGenerator::generate(4);
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
     * Gets: When the hook was deleted
     *
     * @return DateTime deletedAt
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
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
    }
}
