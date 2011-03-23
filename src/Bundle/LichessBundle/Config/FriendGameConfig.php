<?php

namespace Bundle\LichessBundle\Config;
use Bundle\LichessBundle\Document\Game;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class FriendGameConfig extends GameConfig
{
    public $time      = 0;
    public $increment = 0;
    public $variant   = Game::VARIANT_STANDARD;
    public $mode      = 0;
    public $color     = 'random';

    protected $colorChoices = array('white', 'black', 'random');

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('time', new Constraints\Min(array('limit' => 0)));
        $metadata->addPropertyConstraint('increment', new Constraints\Min(array('limit' => 0)));
    }

    public function getColorChoices()
    {
        return array_combine($this->colorChoices, $this->colorChoices);
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
        return $this->modeChoices[$this->mode];
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
