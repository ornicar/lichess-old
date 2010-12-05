<?php

namespace Bundle\LichessBundle\Document;

use Bundle\LichessBundle\Model;

/**
 * @mongodb:Document(
 *   collection="seek",
 *   repositoryClass="Bundle\LichessBundle\Document\SeekRepository"
 * )
 */
class Seek extends Model\Seek
{
    /**
     * Id
     *
     * @var \MongoId
     * @mongodb:Id
     */
    protected $id = null;

    /**
     * Variants accepted
     *
     * @var array
     * @mongodb:Field(type="collection")
     */
    protected $variants;

    /**
     * Times accepted
     *
     * @var array
     * @mongodb:Field(type="collection")
     */
    protected $times;

    /**
     * Game
     *
     * @var Game
     * @mongodb:ReferenceOne(targetDocument="Game")
     */
    protected $game;

    /**
     * Creator session id
     *
     * @var string
     * @mongodb:Field(type="string")
     */
    protected $sessionId = null;

    /**
     * Creation date
     *
     * @var \DateTime
     * @mongodb:Field(type="date")
     * @mongodb:Index(order="desc")
     */
    protected $createdAt = null;
}
