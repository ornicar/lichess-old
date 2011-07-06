<?php

namespace Lichess\OpeningBundle\Config;

use Bundle\LichessBundle\Document\Game;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints;

class AiGameConfig extends FriendGameConfig
{
    /**
     * AI level
     *
     * @var int
     */
    protected $level = 1;

    protected static $levelChoices = array(1, 2, 3, 4, 5, 6, 7, 8);

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        parent::loadValidatorMetadata($metadata);

        $metadata->addPropertyConstraint('level', new Constraints\Choice(array('choices' => array_keys(self::getLevelChoices()))));
        $metadata->addPropertyConstraint('level', new Constraints\NotBlank());
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param  int
     * @return null
     */
    public function setLevel($level)
    {
        $this->level = (int) $level;
    }

    public static function getLevelChoices()
    {
        return array_combine(self::$levelChoices, self::$levelChoices);
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), array('level' => $this->level));
    }

    public function fromArray(array $data)
    {
        parent::fromArray($data);

        if(isset($data['level'])) $this->level = $data['level'];
    }
}
