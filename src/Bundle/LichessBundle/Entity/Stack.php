<?php

namespace Bundle\LichessBundle\Entity;

use Bundle\LichessBundle\Model;

/**
 * @orm:Entity
 */
class Stack extends Model\Stack
{
    /**
     * @orm:Id
     * @orm:Column(type="integer")
     * @orm:GeneratedValue
     */
    protected $id = null;

    /**
     * Events in the stack
     *
     * @var array
     * @orm:Column(type="array")
     */
    protected $events = array();
}
