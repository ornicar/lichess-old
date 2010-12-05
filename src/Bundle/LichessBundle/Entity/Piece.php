<?php

namespace Bundle\LichessBundle\Entity;

use Bundle\LichessBundle\Model;

/**
 * @orm:Entity
 * @orm:InheritanceType("SINGLE_TABLE")
 * @orm:DiscriminatorColumn(name="t", type="string")
 * @orm:DiscriminatorMap({
 *     "p"="Bundle\LichessBundle\Entity\Piece\Pawn",
 *     "r"="Bundle\LichessBundle\Entity\Piece\Rook",
 *     "b"="Bundle\LichessBundle\Entity\Piece\Bishop",
 *     "n"="Bundle\LichessBundle\Entity\Piece\Knight",
 *     "q"="Bundle\LichessBundle\Entity\Piece\Queen",
 *     "k"="Bundle\LichessBundle\Entity\Piece\King"
 * })
 */
abstract class Piece extends Model\Piece
{
    /**
     * @orm:Id
     * @orm:Column(type="integer")
     * @orm:GeneratedValue
     */
    protected $id;

    /**
     * X position
     *
     * @var int
     * @orm:Column(type="integer")
     */
    protected $x = null;

    /**
     * Y position
     *
     * @var int
     * @orm:Column(type="integer")
     */
    protected $y = null;

    /**
     * Whether the piece is dead or not
     *
     * @var boolean
     * @orm:Column(type="boolean")
     */
    protected $isDead = null;

    /**
     * When this piece moved for the first time (useful for en passant)
     *
     * @var int
     * @orm:Column(type="integer")
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
