<?php

namespace Bundle\LichessBundle\Document;

use Bundle\LichessBundle\Model;

/**
 * @mongodb:EmbeddedDocument
 */
class Stack extends Model\Stack
{
    /**
     * Events in the stack
     *
     * @var array
     * @mongodb:Field(type="hash")
     */
    protected $events = array();
}
