<?php

namespace Bundle\LichessBundle\Entity;

use Bundle\LichessBundle\Model;

/**
 * @orm:Entity(repositoryClass="Bundle\LichessBundle\Entity\SeekRepository")
 * @orm:Table(name="seek", indexes={
 *      @orm:index(name="created_idx", columns={"createdAt"})
 * })
 */
class Seek extends Model\Seek
{
    /**
     * Id
     *
     * @var int
     * @orm:Id
     * @orm:Column(type="integer")
     * @orm:GeneratedValue
     */
    protected $id = null;

    /**
     * Variants accepted
     *
     * @var array
     * @orm:Column(type="array")
     */
    protected $variants;

    /**
     * Times accepted
     *
     * @var array
     * @orm:Column(type="array")
     */
    protected $times;

    /**
     * Modes
     *
     * @var array
     * @orm:Column(type="array")
     */
    protected $modes;

    /**
     * Game
     *
     * @var Game
     * @orm:OneToOne(targetEntity="Game")
     */
    protected $game;

    /**
     * Creator session id
     *
     * @var string
     * @orm:Column(type="string")
     */
    protected $sessionId = null;

    /**
     * Creation date
     *
     * @var \DateTime
     * @orm:Column(type="date")
     */
    protected $createdAt = null;
}
