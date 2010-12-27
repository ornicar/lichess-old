<?php

namespace Application\FOS\UserBundle;

use Application\FOS\UserBundle\Document\User;

class Onliner
{
    public function setOnline(User $user)
    {
        return $this->setUsernameOnline($user->getUsername());
    }

    public function setUsernameOnline($username)
    {
        apc_store('online.'.$username, true, $this->getTimeout());
    }

    public function isOnline(User $user)
    {
        return $this->isUsernameOnline($user->getUsername());
    }

    public function isUsernameOnline($username)
    {
        return false !== apc_fetch('online.'.$username);
    }

    protected function getTimeout()
    {
        return 20;
    }
}
