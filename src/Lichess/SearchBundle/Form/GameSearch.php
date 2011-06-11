<?php

namespace Lichess\SearchBundle\Form;

use DateTime;
use Symfony\Component\Validator\Constraints as Assert;

class GameSearch
{
    /**
     * @var DateTime
     * @Assert\Date()
     */
    public $fromDate;
    /**
     * @var DateTime
     * @Assert\Date()
     */
    public $toDate;

    /**
     * @var int
     * @Assert\Choice({"1", "2"})
     */
    public $variant;

    /**
     * @var string
     * @Assert\String()
     */
    public $player;
    /**
     * @var string
     * @Assert\String()
     */
    public $opponent;

    /**
     * @var int
     * @Assert\String()
     */
    public $clockMinutes;
    public $clockIncrements;

    public $nbMoves;

    public $result;
}
