<?php

namespace Bundle\LichessBundle\Document;

/**
 * @mongodb:Document(
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
     * Increments accepted
     *
     * @var array
     * @mongodb:Field(type="collection")
     */
    protected $increments;

    /**
     * Modes accepted
     *
     * @var array
     * @mongodb:Field(type="collection")
     */
    protected $modes;

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

    /**
     * color
     *
     * @var string
     * @mongodb:Field(type="string")
     */
    protected $color = null;

    protected $possibleColors = array('white', 'black', 'random');

    public function __construct(array $variants, array $times, array $increments, array $modes, $color, $sessionId)
    {
        $this->variants   = $variants;
        $this->times      = $times;
        $this->increments = $increments;
        $this->modes      = $modes;
        $this->setColor($color);
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
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param  string
     * @return null
     */
    public function setColor($color)
    {
        if (!in_array($color, $this->possibleColors)) {
            throw new \InvalidArgumentException(sprintf('Not an acceptable color: "%s"', $color));
        }
        $this->color = $color;
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
        foreach (array('color', 'modes', 'variants', 'times', 'increments', 'sessionId') as $property) {
            $array[$property] = $this->$property;
        }

        return $array;
    }
}
