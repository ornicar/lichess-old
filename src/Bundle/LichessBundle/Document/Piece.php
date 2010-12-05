<?php

namespace Bundle\LichessBundle\Document;

use Bundle\LichessBundle\Model;

/**
 * @mongodb:MappedSuperclass
 */
abstract class Piece extends Model\Piece
{
    /**
     * X position
     *
     * @var int
     * @mongodb:Field(type="int")
     */
    protected $x = null;

    /**
     * Y position
     *
     * @var int
     * @mongodb:Field(type="int")
     */
    protected $y = null;

    /**
     * Whether the piece is dead or not
     *
     * @var boolean
     * @mongodb:Field(type="boolean")
     */
    protected $isDead = null;

    /**
     * When this piece moved for the first time (useful for en passant)
     *
     * @var int
     * @mongodb:Field(type="int")
     */
    protected $firstMove = null;

    /**
     * the player that owns the piece
     *
     * @var Player
     */
    protected $player = null;

    /**
     * Performance pointer to the player game board
     *
     * @var Board
     */
    protected $board = null;

    /**
     * Cache of the player color
     * This attribute is not persisted
     *
     * @var string
     */
    protected $color = null;
}
