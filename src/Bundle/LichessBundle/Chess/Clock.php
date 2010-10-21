<?php

namespace Bundle\LichessBundle\Chess;

class Clock
{
    /**
     * Maximum time of the clock per player
     *
     * @var int
     */
    private $limit = null;

    /**
     * Current player color
     *
     * @var string
     */
    private $color = null;

    /**
     * Times for white and black players
     *
     * @var array of int
     */
    private $times = null;

    /**
     * Internal timer
     *
     * @var int
     */
    private $timer = null;

    private $isRunning = null;

    public function __construct($limit)
    {
        $this->limit = (int) $limit;

        if($this->limit < 60*1000) {
            throw new \InvalidArgumentException(sprintf('Invalid time limit "%s"', $limit));
        }

        $this->color = 'white';
        $this->isRunning = false;
    }

    /**
     * Switch to next player
     *
     * @return null
     **/
    public function step()
    {
        $this->times[$this->color] += microtime() - $this->timer;
        $this->color = 'white' === $this->color ? 'black' : 'white';
        $this->timer = microtime();
    }

    /**
     * Start the clock now
     *
     * @return null
     **/
    public function start()
    {
        $this->timer = microtime();
        $this->isRunning = true;
    }

    /**
     * Stop the clock now
     *
     * @return null
     **/
    public function stop()
    {
        $this->isRunning = false;
    }

    /**
     * Tell if the player with this color is out of tim
     *
     * @return boolean
     **/
    public function isOutOfTime($color)
    {
        return $this->getRemainingTime($color) < 0;
    }

    /**
     * Tell the time a player has to finish the game
     *
     * @return int
     **/
    public function getRemainingTime($color)
    {
        return $this->limit - $this->getElapsedTime($color);
    }

    /**
     * Tell the time a player has used
     *
     * @return int
     **/
    public function getElapsedTime($color)
    {
        $time = $this->times[$color];
        if($this->isRunning && $color === $this->color) {
            $time += microtime() - $this->timer;
        }

        return $time;
    }

    /**
     * Get color
     * @return string
     */
    public function getColor()
    {
      return $this->color;
    }

    /**
     * Set color
     * @param  string
     * @return null
     */
    public function setColor($color)
    {
      $this->color = $color;
    }

    /**
     * Tell if the clock is enabled
     *
     * @return boolean
     **/
    public function isEnabled()
    {
        return $this->limit > 0;
    }

    /**
     * Tell if the clock is running
     *
     * @return boolean
     **/
    public function isRunning()
    {
        return $this->isRunning;
    }

    /**
     * Get limit
     * @return int
     */
    public function getLimit()
    {
      return $this->limit;
    }

    /**
     * Set limit
     * @param  int
     * @return null
     */
    public function setLimit($limit)
    {
      $this->limit = $limit;
    }

    /**
     * Get times
     * @return array of int
     */
    public function getTimes()
    {
      return $this->times;
    }

    /**
     * Set times
     * @param  array of int
     * @return null
     */
    public function setTimes($times)
    {
      $this->times = $times;
    }
}
