<?php

namespace Bundle\LichessBundle\Cheat;

use Bundle\LichessBundle\Document\Trial;
use Bundle\LichessBundle\Document\TrialRepository;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\Player;
use Doctrine\ODM\MongoDB\DocumentManager;

class Judge
{
    /**
     * Trial repository
     *
     * @var TrialRepository
     */
    protected $trialRepository;
    protected $objectManager;
    protected $trialScoreCalculator;

    /**
     * Create a new judge
     **/
    public function __construct(TrialRepository $trialRepository, DocumentManager $objectManager, TrialScoreCalculator $trialScoreCalculator)
    {
        $this->trialRepository      = $trialRepository;
        $this->objectManager        = $objectManager;
        $this->trialScoreCalculator = $trialScoreCalculator;
    }

    /**
     * Decide whether or not to open a trial for this game
     *
     * @return null
     **/
    public function study(Game $game)
    {
        if (!$game->getIsRated()) {
            return;
        }
        if ($game->getFullmoveNumber() < 8) {
            return;
        }
        foreach ($game->getPlayers() as $player) {
            $blurFactor  = $this->calculateBlurFactor($player);
            $timePerMove = $this->calculateTimePerMove($game);
            $score = $this->trialScoreCalculator->calculateScore($blurFactor, $timePerMove);
            if ($score > 70) {
                $trial = new Trial();
                $trial->setGame($game);
                $trial->setUser($player->getUser());
                $trial->setScore($score);
                $this->objectManager->persist($trial);
            }
        }
    }

    public function setVerdict(Trial $trial, $verdict)
    {
        $trial->setVerdict($verdict);
    }

    /**
     * Return the average available time per move
     *
     * @return float
     **/
    protected function calculateTimePerMove(Game $game)
    {
        if ($clock = $game->getClock()) {
            $totalTime = $clock->getLimit() + ($game->getFullmoveNumber() * $clock->getIncrement());
            $timePerMove = $totalTime / $game->getFullmoveNumber();
        } else {
            $timePerMove = 60;
        }

        return $timePerMove;
    }

    protected function calculateBlurFactor(Player $player)
    {
        $blurs = $player->getGame()->getBlurs();

        return round(100*max(0, min(1, $blurs[$player->getColor()] / $player->getGame()->getFullmoveNumber())));
    }
}
