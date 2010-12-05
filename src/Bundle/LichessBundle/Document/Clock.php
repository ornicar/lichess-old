<?php

namespace Bundle\LichessBundle\Document;

use Bundle\LichessBundle\Model;

/**
 * @mongodb:EmbeddedDocument
 */
class Clock extends Model\Clock
{
    /**
     * Maximum time of the clock per player
     *
    * @var int
    * @mongodb:Field(type="int")
     */
    protected $limit = null;

    /**
     * Current player color
     *
     * @var string
    * @mongodb:Field(type="string")
     */
    protected $color = null;

    /**
     * Times for white and black players
     *
     * @var array
     * @mongodb:Field(type="hash")
     */
    protected $times = null;

    /**
     * Internal timer
     *
     * @var float
     * @mongodb:Field(type="float")
     */
    protected $timer = null;
}
