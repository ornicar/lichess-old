<?php

namespace Bundle\LichessBundle\Form;
use Bundle\LichessBundle\Document\Game;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class FriendGameConfig extends GameConfig
{
    public $time = 0;
    public $variant = Game::VARIANT_STANDARD;
    public $mode = 0;

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('time', new Constraints\Min(array('limit' => 0)));
    }

    public function getTimeName()
    {
        return $this->renameTime($this->time);
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
        return array('time' => $this->time, 'variant' => $this->variant, 'mode' => $this->mode);
    }

    public function fromArray(array $data)
    {
        if(isset($data['time'])) $this->time = $data['time'];
        if(isset($data['variant'])) $this->variant = $data['variant'];
        if(isset($data['mode'])) $this->mode = $data['mode'];
    }
}
