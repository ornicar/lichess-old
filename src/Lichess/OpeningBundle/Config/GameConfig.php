<?php

namespace Lichess\OpeningBundle\Config;
use Bundle\LichessBundle\Document\Game;

abstract class GameConfig
{
    protected static $timeChoices      = array(2, 5, 10, 20, 0);
    protected static $incrementChoices = array(0, 2, 5, 10, 20);
    protected static $modeChoices      = array(0 => 'Casual', 1 => 'Rated');
    protected static $colorChoices     = array('white', 'black', 'random');

    abstract public function toArray();

    abstract public function fromArray(array $data);

    public static function getModeChoices()
    {
        return self::$modeChoices;
    }

    public static function getVariantChoices()
    {
        $choices = array();
        foreach(Game::getVariantNames() as $code => $name) {
            $choices[$code] = ucfirst($name);
        }

        return $choices;
    }

    public static function getTimeChoices()
    {
        $choices = array();
        foreach(self::$timeChoices as $time) {
            $choices[$time] = self::renameTime($time);
        }

        return $choices;
    }

    public static function getIncrementChoices()
    {
        return array_combine(self::$incrementChoices, self::$incrementChoices);
    }

    public static function getColorChoices()
    {
        return array_combine(self::$colorChoices, self::$colorChoices);
    }

    protected static function renameTime($time)
    {
        if($time) {
            return $time;
        }

        return 'Unlimited';
    }
}
