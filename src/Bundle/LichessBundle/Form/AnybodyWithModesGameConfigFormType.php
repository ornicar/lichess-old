<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Form\FormBuilder;

class AnybodyWithModesGameConfigFormType extends AnybodyGameConfigFormType
{
    protected function getConfigChoices()
    {
        $config = $this->config;
        $configChoices = parent::getConfigChoices();
        $configChoices['modes'] = $config::getModeChoices();

        return $configChoices;
    }
}
