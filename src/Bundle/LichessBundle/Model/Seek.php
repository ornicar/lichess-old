<?php

namespace Bundle\LichessBundle\Model;

abstract class Seek
{
    protected $id = null;

    protected $variants;

    protected $times;

    protected $modes;

    protected $game;

    protected $sessionId = null;

    protected $createdAt = null;

    public function __construct(array $variants, array $times, array $modes, $sessionId)
    {
        $this->variants = $variants;
        $this->times = $times;
        $this->modes = $modes;
        $this->sessionId = $sessionId;
        $this->createdAt = new \DateTime();
    }

    public function match(Seek $seek)
    {
        if($seek->sessionId === $this->sessionId) {
            return false;
        }

        return false !== $this->getCommonVariant($seek) && false !== $this->getCommonTime($seek) && false !== $this->getCommonMode($seek);
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

    public function getCommonMode(Seek $seek)
    {
        $matches = array_values(array_intersect($seek->getModes(), $this->getModes()));

        if(empty($matches)) {
            return false;
        }

        if(1 === count($matches)) {
            return $matches[0];
        }

        return $matches[mt_rand(0, 1)];
    }

    /**
     * @return int
     */
    public function getId()
    {
      return $this->id;
    }

    /**
     * @param  int
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
}