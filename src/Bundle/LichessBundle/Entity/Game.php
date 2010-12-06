<?php

namespace Bundle\LichessBundle\Entity;

use Bundle\LichessBundle\Model;

/**
 * Represents a single Chess game
 *
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 *
 * @orm:Entity(
 *   repositoryClass="Bundle\LichessBundle\Entity\GameRepository"
 * )
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="games", indexes={
 *      @orm:index(name="status_idx", columns={"status"}),
 *      @orm:index(name="winneruserid_idx", columns={"winnerUserId"}),
 *      @orm:index(name="updatedat_idx", columns={"updatedAt"})
 * })
 */
class Game extends Model\Game
{
    /**
     * Unique ID of the game
     *
     * @var string
     * @orm:Id
     * @orm:Column(type="string")
     */
    protected $id;

    /**
     * Game variant (like standard or 960)
     *
     * @var int
     * @orm:Column(type="integer")
     */
    protected $variant;

    /**
     * The current state of the game, like CREATED, STARTED or MATE.
     *
     * @var int
     * @orm:Column(type="integer")
     */
    protected $status;

    /**
     * The two players
     *
     * @var array
     * @orm:OneToMany(targetEntity="Player", mappedBy="game", cascade={"persist", "remove"})
     */
    protected $players;

    /**
     * Ids of the users bound to players
     *
     * @var array
     * @orm:Column(type="array")
     */
    protected $userIds = array();

    /**
     * Id of the user who won the game
     * - string if the winner has a user
     * - false if the winner has no user
     * - null if there is no winner
     *
     * @var string
     * @orm:Column(type="string", nullable=true)
     */
    protected $winnerUserId = null;

    /**
     * Color of the player who created the game
     *
     * @var string
     * @orm:Column(type="string")
     */
    protected $creatorColor;

    /**
     * Number of turns passed
     *
     * @var integer
     * @orm:Column(type="integer")
     */
    protected $turns;

    /**
     * PGN moves of the game
     *
     * @var array
     * @orm:Column(type="array")
     */
    protected $pgnMoves;

    /**
     * The ID of the player that starts the next game the players will play
     *
     * @var string
     * @orm:Column(type="string", nullable=true)
     */
    protected $next;

    /**
     * Fen notation of the initial position
     * Can be null if equals to standard position
     *
     * @var string
     * @orm:Column(type="string", nullable=true)
     */
    protected $initialFen;

    /**
     * Last update time
     *
     * @var \DateTime
     * @orm:Column(type="date")
     */
    protected $updatedAt;

    /**
     * Creation date
     *
     * @var \DateTime
     * @orm:Column(type="date")
     */
    protected $createdAt;

    /**
     * Array of position hashes, used to detect threefold repetition
     *
     * @var array
     * @orm:Column(type="array")
     */
    protected $positionHashes = array();

    /**
     * The game clock
     *
     * @var Clock
     * @orm:OneToOne(targetEntity="Clock", cascade={"persist", "remove"})
     */
    protected $clock;

    /**
     * The chat room
     *
     * @var Room
     * @orm:OneToOne(targetEntity="Room", cascade={"persist", "remove"})
     */
    protected $room;

    /**
     * Whether this game is ranked or not
     *
     * @var bool
     * @orm:Column(type="boolean")
     */
    protected $isRated = false;

    /**
     * The game board
     *
     * @var Board
     */
    protected $board;

    public function getClockInstance($time, $moveBonus = null)
    {
        return new Clock($time, $moveBonus);
    }

    public function getRoomInstance(array $messages = array())
    {
        return new Room($messages);
    }

    /**
     * @orm:PrePersist
     */
    public function setCreatedNow()
    {
        parent::setCreatedNow();
    }

    /**
     * @orm:PreUpdate
     * @orm:PrePersist
     */
    public function setUpdatedNow()
    {
        parent::setUpdatedNow();
    }

    /**
     * @orm:PostLoad
     */
    public function ensureDependencies()
    {
        parent::ensureDependencies();
    }
}
