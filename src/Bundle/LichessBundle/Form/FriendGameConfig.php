<?php

namespace Bundle\LichessBundle\Form;
use Bundle\LichessBundle\Entities\Game;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class FriendGameConfig extends GameConfig
{
    public $time = 0;
    public $variant = Game::VARIANT_STANDARD;

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('time', new Constraints\Min(array('limit' => 0)));
    }

    public function getTimeNames()
    {
        $names = array();
        foreach($this->times as $time) {
            $names[] = $time ? $time.' min.' : 'no clock';
        }

        return $names;
    }

    public function toArray()
    {
        return array('time' => $this->time, 'variant' => $this->variant);
    }

    public function fromArray(array $data)
    {
        if(isset($data['time'])) $this->time = $data['time'];
        if(isset($data['variant'])) $this->variant = $data['variant'];
    }
}
