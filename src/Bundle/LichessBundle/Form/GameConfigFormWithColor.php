<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\Form;

use Symfony\Component\Form\HiddenField;
use Symfony\Component\Form\TextField;

abstract class GameConfigFormWithColor extends GameConfigForm
{
    protected $hasHiddenColor = false;
    protected $possibleColors = array('white', 'black', 'random');
    protected $defaultColor   = 'random';

    public function configure()
    {
        $this->add(new TextField('color'));
    }

    public function addColorHiddenField()
    {
        $this->remove('color');
        $this->add(new HiddenField('color'));
        $this->hasHiddenColor = true;
    }

    public function hasHiddenColor()
    {
        return $this->hasHiddenColor;
    }
}
