<?php

namespace Bundle\LichessBundle\Form;

class AnybodyGameConfig extends GameConfig
{
    public $times = array();

    public function __construct($translator = null)
    {
        parent::__construct($translator);

        $this->times = $this->timeChoices;
    }
}
