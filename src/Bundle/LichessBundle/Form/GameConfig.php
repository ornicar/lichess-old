<?php

namespace Bundle\LichessBundle\Form;
use Bundle\LichessBundle\Entities\Game;

abstract class GameConfig
{
    protected $timeChoices = array(5, 10, 20, 0);
    protected $variantChoices = array(Game::VARIANT_STANDARD, Game::VARIANT_960);
    protected $translator;

    public function __construct($translator = null)
    {
        $this->translator = $translator;
    }

    public function getVariantChoices()
    {
        $choices = array();
        foreach($this->variantChoices as $code) {
            $choices[$code] = Game::getVariantName($code);
        }

        return $choices;
    }

    public function getTimeChoices()
    {
        if(!$this->translator) {
            throw new \LogicException('You must pass a translator instance');
        }

        $choices = array();
        foreach($this->timeChoices as $time) {
            $choices[$time] = $this->getName($time);
        }

        return $choices;
    }

    protected function getName($time)
    {
        return $time ? $this->translator->_('%nb% minutes/side', array('%nb%' => $time)) : $this->translator->_('no clock');
    }
}
