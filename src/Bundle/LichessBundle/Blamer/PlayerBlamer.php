<?php

namespace Bundle\LichessBundle\Blamer;
use Bundle\LichessBundle\Document\Player;
use Symfony\Component\Security\Core\SecurityContext;
use FOS\UserBundle\Model\User;

class PlayerBlamer
{
    protected $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function blame(Player $player)
    {
        if ($token = $this->securityContext->getToken()) {
            if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
                $user = $token->getUser();
                if($user instanceof User) {
                    $player->setUser($user);
                }
            }
        }
    }
}
