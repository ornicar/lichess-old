<?php

namespace Lichess\SearchBundle\Form;

use DateTime;

class GameSearch
{
    /**
     * @var DateTime
     * @validation:Date()
     */
    public $fromDate;
    /**
     * @var DateTime
     * @validation:Date()
     */
    public $toDate;

    /**
     * @var int
     * @validation:Choice({"1", "2"})
     */
    public $variant;

    /**
     * @var string
     * @validation:String()
     */
    public $player;
    /**
     * @var string
     * @validation:String()
     */
    public $opponent;

    /**
     * @var int
     * @validation:String()
     */
    public $clockMinutes;
    public $clockIncrements;

    public $nbMoves;

    public $result;
}
