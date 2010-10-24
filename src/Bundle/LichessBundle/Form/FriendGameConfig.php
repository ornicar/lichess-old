<?php

namespace Bundle\LichessBundle\Form;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class FriendGameConfig extends GameConfig
{
    public $time = 0;

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('time', new Constraints\Min(array('limit' => 0)));
    }
}
