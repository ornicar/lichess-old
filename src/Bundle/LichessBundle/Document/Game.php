<?php

namespace Bundle\LichessBundle\Document;

use Bundle\LichessBundle\Model;

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
class Game extends Model\Game
{
    /**
     * Unique ID of the game
     *
     * @var string
     * @mongodb:Id(strategy="none")
     */
    protected $id;

    /**
     * Game variant (like standard or 960)
     *
     * @var int
     * @mongodb:Field(type="int")
     */
    protected $variant;

    /**
     * The current state of the game, like CREATED, STARTED or MATE.
     *
     * @var int
     * @mongodb:Field(type="int")
     * @mongodb:Index()
     */
    protected $status;

    /**
     * The two players
     *
     * @var array
     * @mongodb:EmbedMany(targetDocument="Player")
     */
    protected $players;

    /**
     * Ids of the users bound to players
     *
     * @var array
     * @mongodb:Field(type="collection")
     * @mongodb:Index()
     */
    protected $userIds = array();

    /**
     * Id of the user who won the game
     * - string if the winner has a user
     * - false if the winner has no user
     * - null if there is no winner
     *
     * @var string
     * @mongodb:Field(type="string")
     * @mongodb:Index()
     */
    protected $winnerUserId = null;

    /**
     * Color of the player who created the game
     *
     * @var string
     * @mongodb:Field(type="string")
     */
    protected $creatorColor;

    /**
     * Number of turns passed
     *
     * @var integer
     * @mongodb:Field(type="int")
     */
    protected $turns;

    /**
     * PGN moves of the game
     *
     * @var array
     * @mongodb:Field(type="collection")
     */
    protected $pgnMoves;

    /**
     * The ID of the player that starts the next game the players will play
     *
     * @var string
     * @mongodb:Field(type="string")
     */
    protected $next;

    /**
     * Fen notation of the initial position
     * Can be null if equals to standard position
     *
     * @var string
     * @mongodb:Field(type="string")
     */
    protected $initialFen;

    /**
     * Last update time
     *
     * @var \DateTime
     * @mongodb:Field(type="date")
     * @mongodb:Index(order="desc")
     */
    protected $updatedAt;

    /**
     * Creation date
     *
     * @var \DateTime
     * @mongodb:Field(type="date")
     */
    protected $createdAt;

    /**
     * Array of position hashes, used to detect threefold repetition
     *
     * @var array
     * @mongodb:Field(type="collection")
     */
    protected $positionHashes = array();

    /**
     * The game clock
     *
     * @var Clock
     * @mongodb:EmbedOne(targetDocument="Clock", nullable=true)
     */
    protected $clock;

    /**
     * The chat room
     *
     * @var Room
     * @mongodb:EmbedOne(targetDocument="Room", nullable=true)
     */
    protected $room;

    /**
     * The game board
     *
     * @var Board
     */
    protected $board;

    protected function getClockInstance($time)
    {
        return new Clock($time);
    }

    protected function getRoomInstance()
    {
        return new Room();
    }
}
