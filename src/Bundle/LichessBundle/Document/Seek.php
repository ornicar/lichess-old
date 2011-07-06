<?php

namespace Bundle\LichessBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(
 *   collection="seek",
 *   repositoryClass="Bundle\LichessBundle\Document\SeekRepository"
 * )
 */
class Seek
{
    /**
     * Id
     *
     * @var \MongoId
     * @MongoDB\Id
     */
    protected $id = null;

    /**
     * Variants accepted
     *
     * @var array
     * @MongoDB\Field(type="collection")
     */
    protected $variants;

    /**
     * Times accepted
     *
     * @var array
     * @MongoDB\Field(type="collection")
     */
    protected $times;

    /**
     * Increments accepted
     *
     * @var array
     * @MongoDB\Field(type="collection")
     */
    protected $increments;

    /**
     * Modes accepted
     *
     * @var array
     * @MongoDB\Field(type="collection")
     */
    protected $modes;

    /**
     * Game
     *
     * @var Game
     * @MongoDB\ReferenceOne(targetDocument="Game")
     */
    protected $game;

    /**
     * Creator session id
     *
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $sessionId = null;

    /**
     * Creation date
     *
     * @var \DateTime
     * @MongoDB\Field(type="date")
     * @MongoDB\Index(order="desc")
     */
    protected $createdAt = null;

    public function __construct(array $variants, array $times, array $increments, array $modes, $sessionId)
    {
        $this->variants   = $variants;
        $this->times      = $times;
        $this->increments = $increments;
        $this->modes      = $modes;
        $this->sessionId  = $sessionId;
        $this->createdAt  = new \DateTime();
    }

    /**
     * @return \MongoId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  \MongoId
     * @return null
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param  string
     * @return null
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }
    /**
     * @return Game
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @param  Game
     * @return null
     */
    public function setGame($game)
    {
        $this->game = $game;
    }

    /**
     * @return array
     */
    public function getTimes()
    {
        return $this->times;
    }

    /**
     * @param  array
     * @return null
     */
    public function setTimes($times)
    {
        $this->times = $times;
    }

    /**
     * @return array
     */
    public function getIncrements()
    {
        return $this->increments;
    }

    /**
     * @param  array
     * @return null
     */
    public function setIncrements($increments)
    {
        $this->increments = $increments;
    }

    /**
     * @return array
     */
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * @param  array
     * @return null
     */
    public function setVariants($variants)
    {
        $this->variants = $variants;
    }

    /**
     * @return array
     */
    public function getModes()
    {
        return $this->modes;
    }

    /**
     * @param  array
     * @return null
     */
    public function setModes($modes)
    {
        $this->modes = $modes;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function toArray()
    {
        $array = array();
        foreach (array('modes', 'variants', 'times', 'increments', 'sessionId') as $property) {
            $array[$property] = $this->$property;
        }

        return $array;
    }
}
