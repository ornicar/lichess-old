<?php

namespace Lichess\OpeningBundle\Config;
use Bundle\LichessBundle\Document\Game;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class FriendGameConfig extends GameConfig
{
    /**
     * Clock time in minutes
     *
     * @var int
     */
    protected $time = 0;

    /**
     * Clock increment in seconds
     *
     * @var int
     */
    protected $increment = 0;

    /**
     * Game variant code
     *
     * @var int
     */
    protected $variant = Game::VARIANT_STANDARD;

    /**
     * Casual{0} or rated {1}
     *
     * @var int
     */
    protected $mode = 0;

    /**
     * Creator player color
     *
     * @var string
     */
    protected $color = 'random';

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('time', new Constraints\Choice(array('choices' => array_keys(self::getTimeChoices()))));
        $metadata->addPropertyConstraint('time', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('increment', new Constraints\Choice(array('choices' => array_keys(self::getIncrementChoices()))));
        $metadata->addPropertyConstraint('increment', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('color', new Constraints\Choice(array('choices' => array_keys(self::getColorChoices()))));
        $metadata->addPropertyConstraint('color', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('variant', new Constraints\Choice(array('choices' => array_keys(self::getVariantChoices()))));
        $metadata->addPropertyConstraint('variant', new Constraints\NotBlank());
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

    public function resolveColor()
    {
        if ('random' == $this->color) {
            return mt_rand(0, 1) ? 'white' : 'black';
        }

        return $this->color;
    }

    public function getTimeName()
    {
        return $this->renameTime($this->time);
    }

    public function getIncrementName()
    {
        return $this->increment;
    }

    public function getVariantName()
    {
        $variantNames = Game::getVariantNames();

        return ucfirst($variantNames[$this->variant]);
    }

    public function getModeName()
    {
        return self::$modeChoices[$this->mode];
    }

    public function toArray()
    {
        return array('time' => $this->time, 'increment' => $this->increment, 'variant' => $this->variant, 'mode' => $this->mode);
    }

    public function fromArray(array $data)
    {
        if(isset($data['time'])) $this->time = $data['time'];
        if(isset($data['increment'])) $this->increment = $data['increment'];
        if(isset($data['variant'])) $this->variant = $data['variant'];
        if(isset($data['mode'])) $this->mode = $data['mode'];
    }
}
