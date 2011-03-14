<?php

namespace Bundle\LichessBundle\Config;

use Application\UserBundle\Document\User;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\Security\Core\SecurityContext;

class Persistence
{
    protected $session;
    protected $securityContext;

    public function __construct(Session $session, SecurityContext $securityContext)
    {
        $this->session = $session;
        $this->securityContext = $securityContext;
    }

    public function loadConfigFor($mode)
    {
        if ($config = $this->session->get($this->getSessionKeyFor($mode))) {
            return $config;
        }
        if ($user = $this->getUser()) {
            if ($config = $user->getGameConfig($mode)) {
                return $config;
            }
        }

        return array();
    }

    public function saveConfigFor($mode, array $config)
    {
        $this->session->set($this->getSessionKeyFor($mode), $config);

        if ($user = $this->getUser()) {
            $user->setGameConfig($mode, $config);
        }
    }

    protected function getUser()
    {
        if ($token = $this->securityContext->getToken()) {
            if ($this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                return $token->getUser();
            }
        }
    }

    protected function getSessionKeyFor($mode)
    {
        return 'lichess.game_config.'.$mode;
    }
}
