<?php

namespace Bundle\LichessBundle\Form;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AnybodyGameConfig extends GameConfig
{
    public $times = array();

    public function __construct($translator = null)
    {
        parent::__construct($translator);

        $this->times = $this->timeChoices;
    }

    public function getCountTimes()
    {
        return count($this->times);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addGetterConstraint('countTimes', new Constraints\Min(array('limit' => 1)));
    }
}
