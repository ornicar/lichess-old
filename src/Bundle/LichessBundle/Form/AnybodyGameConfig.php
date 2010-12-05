<?php

namespace Bundle\LichessBundle\Form;
use Bundle\LichessBundle\Model\Game;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AnybodyGameConfig extends GameConfig
{
    public $times = array(20, 0);
    public $variants = array(Game::VARIANT_STANDARD);

    public function getCountTimes()
    {
        return count($this->times);
    }

    public function getCountVariants()
    {
        return count($this->variants);
    }

    public function getTimeNames()
    {
        $names = array();
        foreach($this->times as $time) {
            $names[] = $time ? $time.' min.' : 'no clock';
        }

        return $names;
    }

    public function getVariantNames()
    {
        $variantNames = Game::getVariantNames();
        $names = array();
        foreach($this->variants as $variant) {
            $names[] = $variantNames[$variant];
        }

        return $names;
    }

    public function renderTimeNames()
    {
        return implode(' or ', $this->getTimeNames());
    }

    public function renderVariantNames()
    {
        return implode(' or ', $this->getVariantNames());
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addGetterConstraint('countTimes', new Constraints\Min(array('limit' => 1)));
        $metadata->addGetterConstraint('countVariants', new Constraints\Min(array('limit' => 1)));
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
