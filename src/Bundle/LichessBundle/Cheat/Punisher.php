<?php

namespace Bundle\LichessBundle\Cheat\Punisher;

use Bundle\LichessBundle\Document\GameRepository;
use Bundle\DoctrineUserBundle\Model\User;

class Punisher
{
    protected $gameRepository;

    public function __construct(GameRepository $gameRepository)
    {
        $this->gameRepository = $gameRepository;
    }

    public function punish(User $user)
    {
    }
}
