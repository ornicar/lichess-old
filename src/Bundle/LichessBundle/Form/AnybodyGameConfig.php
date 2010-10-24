<?php

namespace Bundle\LichessBundle\Form;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AnybodyGameConfig extends GameConfig
{
    public $times = array(0);

    public function getCountTimes()
    {
        return count($this->times);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addGetterConstraint('countTimes', new Constraints\Min(array('limit' => 1)));
    }
}
