<?php

namespace Bundle\LichessBundle\Blamer;
use Bundle\LichessBundle\Document\Player;
use Symfony\Component\Security\SecurityContext;
use Symfony\Component\Security\User\AdvancedAccountInterface;

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
        if($user instanceof AdvancedAccountInterface && $user->hasRole('IS_AUTHENTICATED_FULLY')) {
            $player->setUser($user);
        }
    }
}

