<?php

namespace Lichess\OpeningBundle\Config;

use Symfony\Component\Validator\Constraints as Assert;

class GameConfig
{
    /**
     * Whether or not to use a clock
     *
     * @Assert\Type("boolean")
     * @var boolean
     */
    protected $clock = false;

    /**
     * Clock time in minutes
     *
     * @Assert\NotBlank()
     * @Assert\Min(0)
     * @Assert\Max(30)
     * @var int
     */
    protected $time = 5;

    /**
     * Clock increment in seconds
     *
     * @Assert\NotBlank()
     * @Assert\Min(0)
     * @Assert\Max(30)
     * @var int
     */
    protected $increment = 8;

    /**
     * Game variant code
     *
     * @Assert\NotBlank()
     * @Assert\Regex(pattern="/^(1|2)$/")
     * @var string
     */
    protected $variant = 1;

    /**
     * casual or rated
     *
     * @Assert\NotBlank()
     * @Assert\Regex(pattern="/^(0|1)$/")
     * @var string
     */
    protected $mode = 0;

    /**
     * Creator player color
     *
     * @Assert\NotBlank()
     * @Assert\Regex(pattern="/^(white|black|random)$/")
     * @var string
     */
    protected $color = 'random';

    /**
     * @Assert\Regex(pattern="/^\d{3,4}\-\d{3,4}$/")
     * @var string
     */
    protected $eloRange = null;

    /**
     * There is no clock, or ther is some time in it
     *
     * @Assert\True()
     */
    public function isClockValid()
    {
        return !$this->getClock() || $this->getTime() || $this->getIncrement();
    }

    public function resolveColor()
    {
        if ('random' == $this->color) {
            return mt_rand(0, 1) ? 'white' : 'black';
        }

        return $this->color;
    }

    public function toArray()
    {
        return array('clock' => $this->clock, 'time' => $this->time, 'increment' => $this->increment, 'variant' => $this->variant, 'mode' => $this->mode, 'color' => $this->color, 'eloRange' => $this->eloRange);
    }

    public function fromArray(array $data)
    {
        if(isset($data['clock'])) $this->clock = (boolean) $data['clock'];
        if(isset($data['time'])) $this->time = $data['time'];
        if(isset($data['increment'])) $this->increment = $data['increment'];
        if(isset($data['variant'])) $this->variant = $data['variant'];
        if(isset($data['mode'])) $this->mode = $data['mode'];
        if(isset($data['color'])) $this->color = $data['color'];
        if(isset($data['eloRange'])) $this->eloRange = $data['eloRange'];
    }

    public function createView()
    {
        return new GameConfigView($this->toArray());
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param  int
     * @return null
     */
    public function setTime($time)
    {
        $this->time = (int) $time;
    }

    /**
     * @return int
     */
    public function getIncrement()
    {
        return $this->increment;
    }

    /**
     * @param  int
     * @return null
     */
    public function setIncrement($increment)
    {
        $this->increment = (int) $increment;
    }

    /**
     * @return int
     */
    public function getVariant()
    {
        return $this->variant;
    }

    /**
     * @param  int
     * @return null
     */
    public function setVariant($variant)
    {
        $this->variant = (int) $variant;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param  int
     * @return null
     */
    public function setMode($mode)
    {
        $this->mode = (int) $mode;
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
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getEloRange()
    {
        return $this->eloRange;
    }

    /**
     * @param  string
     * @return null
     */
    public function setEloRange($eloRange)
    {
        $this->eloRange = $eloRange;
    }

    public function hasEloRange()
    {
        return (bool) $this->eloRange;
    }

    public function getClock()
    {
        return $this->clock;
    }

    public function setClock($clock)
    {
        $this->clock = (boolean) $clock;
    }
}
