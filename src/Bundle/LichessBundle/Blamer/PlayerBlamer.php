<?php

namespace Bundle\LichessBundle\Blamer;
use Bundle\LichessBundle\Document\Player;
use Symfony\Component\Security\SecurityContext;
use Symfony\Component\Security\User\AdvancedAccountInterface;
use Symfony\Component\HttpFoundation\Session;

class PlayerBlamer
{
    protected $securityContext;
    protected $session;

    public function __construct(SecurityContext $securityContext, Session $session)
    {
        $this->securityContext = $securityContext;
        $this->session = $session;
    }

    public function blame(Player $player)
    {
        $user = $this->securityContext->getUser();
        if($user instanceof AdvancedAccountInterface && $user->hasRole('IS_AUTHENTICATED_FULLY')) {
            $player->setUser($user);
        }

        $player->setSession($this->session->get('lichess.session_id'));
    }
}

