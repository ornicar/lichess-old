<?php

namespace Lichess\OpeningBundle\Config;
use Bundle\LichessBundle\Document\Game;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AnybodyGameConfig extends GameConfig
{
    protected $modes      = array(0, 1);
    protected $times      = array(5, 10, 20, 0);
    protected $increments = array(2, 5, 10);
    protected $variants   = array(Game::VARIANT_STANDARD);

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $propertyChoices = array(
            'times'      => array_keys(self::getTimeChoices()),
            'modes'      => array_keys(self::getModeChoices()),
            'increments' => array_keys(self::getIncrementChoices()),
            'variants'   => array_keys(self::getVariantChoices())
        );
        foreach ($propertyChoices as $property => $choices) {
            $metadata->addPropertyConstraint($property, new Constraints\Choice(array(
                'choices'  => $choices,
                'multiple' => true,
                //'min' => 1
            )));
        }
    }

    /**
     * @return array
     */
    public function getTimes()
    {
        return $this->times;
    }

    /**
     * @param  array
     * @return null
     */
    public function setTimes(array $times)
    {
        $this->times = $times;
    }

    /**
     * @return array
     */
    public function getIncrements()
    {
        return $this->increments;
    }

    /**
     * @param  array
     * @return null
     */
    public function setIncrements(array $increments)
    {
        $this->increments = $increments;
    }

    /**
     * @return array
     */
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * @param  array
     * @return null
     */
    public function setVariants(array $variants)
    {
        $this->variants = $variants;
    }

    /**
     * @return array
     */
    public function getModes()
    {
        return $this->modes;
    }

    /**
     * @param  array
     * @return null
     */
    public function setModes(array $modes)
    {
        $this->modes = $modes;
    }

    public function getCountTimes()
    {
        return count($this->times);
    }

    public function getCountIncrements()
    {
        return count($this->increments);
    }

    public function getCountVariants()
    {
        return count($this->variants);
    }

    public function getCountModes()
    {
        return count($this->modes);
    }

    public function getTimeNames()
    {
        $names = array();
        foreach($this->times as $time) {
            $names[] = self::renameTime($time);
        }

        return $names;
    }

    public function getIncrementNames()
    {
        return $this->increments;
    }

    public function getVariantNames()
    {
        $variantNames = Game::getVariantNames();
        $names = array();
        foreach($this->variants as $variant) {
            $names[] = ucfirst($variantNames[$variant]);
        }

        return $names;
    }

    public function getModeNames()
    {
        $names = array();
        foreach($this->modes as $mode) {
            $names[] = self::$modeChoices[$mode];
        }

        return $names;
    }

    public function toArray()
    {
        return array('times' => $this->times, 'increments' => $this->increments, 'variants' => $this->variants, 'modes' => $this->modes);
    }

    public function fromArray(array $data)
    {
        if(isset($data['times'])) $this->times = $data['times'];
        if(isset($data['increments'])) $this->increments = $data['increments'];
        if(isset($data['variants'])) $this->variants = $data['variants'];
        if(isset($data['modes'])) $this->modes = $data['modes'];
    }
}
