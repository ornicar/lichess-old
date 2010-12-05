<?php

namespace Bundle\LichessBundle\Entity;

use Bundle\LichessBundle\Model;

/**
 * @orm:Entity
 */
class Room extends Model\Room
{
    /**
     * @orm:Id
     * @orm:Column(type="integer")
     * @orm:GeneratedValue
     */
    protected $id;
    
    /**
     * List of room messages
     *
     * @var array
     * @orm:Column(type="array")
     */
    protected $messages = array();
}
