<?php

namespace Application\UserBundle;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Session;
use Application\UserBundle\Document\User;

class Settings
{
    public function __construct(SecurityContextInterface $security, Session $session)
    {
        $this->security = $security;
        $this->session = $session;
    }

    public function toggle($name, $default)
    {
        return $this->set($name, !$this->get($name, !$default));
    }

    public function get($name, $default)
    {
        if ($this->session->has($name)) {
            return $this->session->get($name);
        } elseif ($user = $this->getUser()) {
            return $user->getSetting($name, $default);
        } else {
            return $default;
        }
    }

    public function set($name, $value)
    {
        $this->session->set($name, $value);
        if ($user = $this->getUser()) {
            $user->setSetting($name, $value);
        }

        return $value;
    }

    private function getUser()
    {
        $user = $this->security->getToken()->getUser();

        return $user instanceof User ? $user : null;
    }
}
