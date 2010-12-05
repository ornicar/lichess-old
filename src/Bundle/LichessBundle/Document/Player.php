<?php

namespace Bundle\LichessBundle\Document;

use Bundle\LichessBundle\Model;
/**
 * Represents a single Chess player for one game
 *
 * @mongodb:EmbeddedDocument
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class Player extends Model\Player
{
    /**
     * Unique ID of the player for this game
     *
     * @var string
     * @mongodb:Field(type="string")
     */
    protected $id;

    /**
     * User bound to the player - optional
     *
     * @var User
     * @mongodb:ReferenceOne(targetDocument="Application\DoctrineUserBundle\Document\User")
     */
    protected $user = null;

    /**
     * Fixed ELO of the player user, if any
     *
     * @var int
     * @mongodb:Field(type="int")
     */
    protected $elo = null;

    /**
     * the player color, white or black
     *
     * @var string
     * @mongodb:Field(type="string")
     */
    protected $color;

    /**
     * Whether the player won the game or not
     *
     * @var boolean
     * @mongodb:Field(type="boolean")
     */
    protected $isWinner;

    /**
     * Whether this player is an Artificial intelligence or not
     *
     * @var boolean
     * @mongodb:Field(type="boolean")
     */
    protected $isAi;

    /**
     * If the player is an AI, its level represents the AI intelligence
     *
     * @var int
     * @mongodb:Field(type="int")
     */
    protected $aiLevel;

    /**
     * Event stack
     *
     * @var Stack
     * @mongodb:EmbedOne(targetDocument="Stack")
     */
    protected $stack;

    /**
     * the player pieces
     *
     * @var Collection
     * @mongodb:EmbedMany(
     *   discriminatorMap={
     *     "p"="Bundle\LichessBundle\Document\Piece\Pawn",
     *     "r"="Bundle\LichessBundle\Document\Piece\Rook",
     *     "b"="Bundle\LichessBundle\Document\Piece\Bishop",
     *     "n"="Bundle\LichessBundle\Document\Piece\Knight",
     *     "q"="Bundle\LichessBundle\Document\Piece\Queen",
     *     "k"="Bundle\LichessBundle\Document\Piece\King"
     *   },
     *   discriminatorField="t"
     * )
     */
    protected $pieces;

    /**
     * Whether the player is offering draw or not
     *
     * @var bool
     * @mongodb:Field(type="boolean")
     */
    protected $isOfferingDraw = null;

    /**
     * the player current game
     *
     * @var Game
     */
    protected $game;

    protected function getStackInstance()
    {
        return new Stack();
    }
}
