<?php

namespace Bundle\LichessBundle\Blamer;
use Bundle\LichessBundle\Document\Player;
use Symfony\Component\Security\SecurityContext;
use Bundle\DoctrineUserBundle\Model\User;

class PlayerBlamer
{
    protected $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function blame(Player $player)
    {
        $user = $this->securityContext->getUser();
        if($user instanceof User) {
            $player->setUser($user);
        }
    }
}

