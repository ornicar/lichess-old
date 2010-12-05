<?php

namespace Bundle\LichessBundle\Entity;

use Bundle\LichessBundle\Model;

/**
 * Represents a single Chess player for one game
 *
 * @orm:Entity
 * @author     Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class Player extends Model\Player
{
    /**
     * Unique ID of the player for this game
     *
     * @var string
     * @orm:Id
     * @orm:Column(type="string")
     */
    protected $id;

    /**
     * User bound to the player - optional
     *
     * @var User
     * @orm:OnetoOne(targetEntity="Application\DoctrineUserBundle\Entity\User")
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
     * @orm:OneToOne(targetEntity="Stack", cascade={"persist", "remove"})
     */
    protected $stack;

    /**
     * the player pieces
     *
     * @var Collection
     * @orm:OneToMany(targetEntity="Piece", mappedBy="player", cascade={"persist", "remove"})
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

    protected function getStackInstance()
    {
        return new Stack();
    }
}
