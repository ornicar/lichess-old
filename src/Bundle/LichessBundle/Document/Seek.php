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

    public function __construct(array $variants, array $times, $sessionId)
    {
        $this->variants = $variants;
        $this->times = $times;
        $this->sessionId = $sessionId;
        $this->createdAt = new \DateTime();
    }

    public function match(Seek $seek)
    {
        if($seek->sessionId === $this->sessionId) {
            return false;
        }

        return false !== $this->getCommonVariant($seek) && false !== $this->getCommonTime($seek);
    }

    public function getCommonVariant(Seek $seek)
    {
        $matches = array_values(array_intersect($seek->getVariants(), $this->getVariants()));

        if(empty($matches)) {
            return false;
        }

        if(1 === count($matches)) {
            return $matches[0];
        }

        return $matches[mt_rand(0, 1)];
    }

    public function getCommonTime(Seek $seek)
    {
        $matches = array_values(array_intersect($seek->getTimes(), $this->getTimes()));

        if(empty($matches)) {
            return false;
        }

        if(count($matches) < 3) {
            return $matches[0];
        }

        return $matches[1];
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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
      return $this->createdAt;
    }

    /**
     * @param  \DateTime
     * @return null
     */
    public function setCreatedAt($createdAt)
    {
      $this->createdAt = $createdAt;
    }
}
