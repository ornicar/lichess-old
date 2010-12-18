<?php

namespace Application\DoctrineUserBundle;

use Application\DoctrineUserBundle\Document\User;

class Onliner
{
    public function setOnline(User $user)
    {
        apc_store($this->getCacheKey($user), true, $this->getTimeout());
    }

    public function setUuidOnline($uuid)
    {
        apc_store($uuid.'.online', true, $this->getTimeout());
    }

    public function isOnline($user)
    {
        return false !== apc_fetch($this->getCacheKey($user));
    }

    protected function getCackeKey(User $user)
    {
        return sprintf('%s.online', $user->getId());
    }

    protected function getTimeout()
    {
        return 20;
    }
}
