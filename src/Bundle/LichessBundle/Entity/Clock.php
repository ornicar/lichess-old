<?php

namespace Bundle\LichessBundle\Entity;

use Bundle\LichessBundle\Model;

/**
 * @orm:Entity
 */
class Clock extends Model\Clock
{
    /**
     * @orm:Id
     * @orm:Column(type="integer")
     * @orm:GeneratedValue
     */
    protected $id;
    
    /**
     * Maximum time of the clock per player
     *
    * @var int
    * @orm:Column(type="integer",name="`limit`")
     */
    protected $limit = null;

    /**
     * Current player color
     *
     * @var string
    * @orm:Column(type="string")
     */
    protected $color = null;

    /**
     * Times for white and black players
     *
     * @var array
     * @orm:Column(type="array")
     */
    protected $times = null;

    /**
     * Internal timer
     *
     * @var float
     * @orm:Column(type="float", nullable=true)
     */
    protected $timer = null;
}
