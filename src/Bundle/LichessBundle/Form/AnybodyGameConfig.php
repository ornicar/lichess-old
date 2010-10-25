<?php

namespace Bundle\LichessBundle\Form;
use Bundle\LichessBundle\Entities\Game;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AnybodyGameConfig extends GameConfig
{
    public $times = array(0);
    public $variants = array(Game::VARIANT_STANDARD);

    public function getCountTimes()
    {
        return count($this->times);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addGetterConstraint('countTimes', new Constraints\Min(array('limit' => 1)));
    }

    public function toArray()
    {
        return array('times' => $this->times, 'variants' => $this->variants);
    }

    public function fromArray(array $data)
    {
        if(isset($data['times'])) $this->times = $data['times'];
        if(isset($data['variants'])) $this->variants = $data['variants'];
    }
}
