<?php

namespace Bundle\LichessBundle\Document;

use Bundle\LichessBundle\Model;

/**
 * @mongodb:EmbeddedDocument
 */
class Room extends Model\Room
{
    /**
     * List of room messages
     *
     * @var array
     * @mongodb:Field(type="collection")
     */
    protected $messages = array();
}
