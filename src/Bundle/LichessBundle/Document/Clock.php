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
    private $l = null;

    /**
     * Current player color
     *
     * @var string
     * @MongoDB\Field(type="string")
     */
    private $c = null;

    /**
     * Times for white player
     *
     * @var int
     * @MongoDB\Float
     */
    private $w = null;

    /**
     * Times for black player
     *
     * @var int
     * @MongoDB\Float
     */
    private $b = null;

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
    protected $i;

    public function __construct($limit, $increment)
    {
        $this->l = (int) $limit;
        $this->i = (int) $increment;

        if (0 === $this->l) {
            $this->l = max(2, $this->i);
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
        $this->c = 'white';
        $this->w = 0;
        $this->b = 0;
        $this->timer = null;
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
        $time = $this->l - $this->getElapsedTime($color);

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

        if($this->isRunning() && $color === $this->c) {
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
        if ($color === 'white') return $this->w;
        elseif ($color === 'black') return $this->b;
        else throw new \Exception("Wrong color $color");
    }

    /**
     * Get increment
     * @return int
     */
    public function getIncrement()
    {
        return $this->i;
    }

    /**
     * Get color
     * @return string
     */
    public function getColor()
    {
        return $this->c;
    }

    /**
     * Tell if the clock is enabled
     *
     * @return boolean
     **/
    public function isEnabled()
    {
        return $this->l > 0;
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
        return $this->l;
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
            'white' => $this->w,
            'black' => $this->b
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
