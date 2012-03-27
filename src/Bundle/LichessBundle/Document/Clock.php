<?php

namespace Bundle\LichessBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class Clock
{
    /**
     * Maximum time of the clock per player
     *
     * @var int
     * @MongoDB\Field(type="int")
     */
    private $limit = null;

    /**
     * Current player color
     *
     * @var string
     * @MongoDB\Field(type="string")
     */
    private $color = null;

    /**
     * Times for white player
     *
     * @var int
     * @MongoDB\Float
     */
    private $white = null;

    /**
     * Times for black player
     *
     * @var int
     * @MongoDB\Float
     */
    private $black = null;

    /**
     * Internal timer
     *
     * @var float
     * @MongoDB\Float
     */
    private $timer = null;

    /**
     *  Assume that a move takes some time to go from player1 -> server -> player2
     *  and remove this time from each move time
     */
    const HTTP_DELAY = 0.5;

    /**
     * Fisher clock bonus per move in seconds
     *
     * @var int
     * @MongoDB\Field(type="int")
     */
    protected $increment;

    public function __construct($limit, $increment)
    {
        $this->limit = (int) $limit;
        $this->increment = (int) $increment;

        if (0 === $this->limit) {
            $this->limit = max(2, $this->increment);
        }

        $this->reset();
    }

    /**
     * Name of the clock
     *
     * @return string
     **/
    public function getName()
    {
        return sprintf('%d minutes/side + %d seconds/move', $this->getLimitInMinutes(), $this->getIncrement());
    }

    /**
     * initializes the clock
     *
     * @return null
     **/
    public function reset()
    {
        $this->color = 'white';
        $this->white = 0;
        $this->black = 0;
        $this->timer = null;
    }

    /**
     * Switch to next player
     *
     * @return null
     **/
    public function step()
    {
        if(!$this->isRunning()) {
            throw new \LogicException('Can not step clock as it is not running');
        }
        // Get absolute time
        $moveTime = microtime(true) - $this->timer;
        // Substract http delay
        $moveTime = max(0, $moveTime - static::HTTP_DELAY);
        // Substract move bonus
        $moveTime -= $this->increment;
        // Update player time
        $this->addTime($this->color, $moveTime);
        $this->color = 'white' === $this->color ? 'black' : 'white';
        $this->timer = microtime(true);
    }

    /**
     * Start the clock now
     * This gives white the time bonus if any.
     *
     * @return null
     **/
    public function start()
    {
        $this->addTime('white', - $this->increment);
        $this->timer = microtime(true);
    }

    /**
     * Stop the clock now
     *
     * @return null
     **/
    public function stop()
    {
        if($this->isRunning()) {
            $this->addTime($this->color, microtime(true) - $this->timer);
            $this->timer = null;
        }
    }

    public function addTime($color, $time)
    {
        $this->setTime($color, round($this->getTime($color) + $time, 2));
    }

    public function giveTime($color, $time)
    {
        $this->setTime($color, round($this->getTime($color) - $time, 2));
    }

    /**
     * Tell if the player with this color is out of tim
     *
     * @return boolean
     **/
    public function isOutOfTime($color)
    {
        return 0 === $this->getRemainingTime($color);
    }

    /**
     * Tell the time a player has to finish the game
     *
     * @return float
     **/
    public function getRemainingTime($color)
    {
        $time = $this->limit - $this->getElapsedTime($color);

        return max(0, round($time, 3));
    }

    /**
     * Tell the time a player has used
     *
     * @return float
     **/
    public function getElapsedTime($color)
    {
        $time = $this->getTime($color);

        if($this->isRunning() && $color === $this->color) {
            $time += microtime(true) - $this->timer;
        }

        return round($time, 3);
    }

    public function getRemainingTimes()
    {
        return array(
            'white' => $this->getRemainingTime('white'),
            'black' => $this->getRemainingTime('black')
        );
    }

    private function getTime($color)
    {
        if ($color === 'white') return $this->white;
        elseif ($color === 'black') return $this->black;
        else throw new \Exception("Wrong color $color");
    }

    private function setTime($color, $time)
    {
        if ($color === 'white') $this->white = $time;
        elseif ($color === 'black') $this->black = $time;
        else throw new \Exception("Wrong color $color");
    }

    /**
     * Get increment
     * @return int
     */
    public function getIncrement()
    {
        return $this->increment;
    }

    /**
     * Set increment
     * @param  int
     * @return null
     */
    public function setIncrement($increment)
    {
        $this->increment = (int) $increment;
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
        return null !== $this->timer;
    }

    /**
     * Get limit
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    public function getLimitInMinutes()
    {
        return round($this->getLimit()/60, 1);
    }

    /**
     * Get times
     * @return array
     */
    public function getTimes()
    {
        return array(
            'white' => $this->white,
            'black' => $this->black
        );
    }

    public function renderTime($time)
    {
        return sprintf('%02d:%02d', floor($time/60), $time%60);
    }

    public function estimateTotalTime()
    {
        return $this->getLimit() + (30 * $this->getIncrement());
    }

    public function __clone()
    {
        $this->reset();
    }

    public function __toString() {
        return 'clock';
    }
}
