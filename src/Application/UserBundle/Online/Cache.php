<?php

namespace Application\UserBundle\Online;

use Application\UserBundle\Document\User;

class Cache
{
    protected $timeout;

    public function __construct($timeout)
    {
        $this->timeout = (int) $timeout;
    }

    public function setOnline(User $user)
    {
        return $this->setUsernameOnline($user->getUsernameCanonical());
    }

    public function setUsernameOnline($username)
    {
        apc_store('online.'.$username, true, $this->timeout);
    }

    public function isOnline(User $user)
    {
        return $this->isUsernameOnline($user->getUsernameCanonical());
    }

    public function isUsernameOnline($username)
    {
        return false !== apc_fetch('online.'.$username);
    }

    public function getOnlineUsernames()
    {
        $it = new \APCIterator('user', '/^online\./', APC_ITER_MTIME | APC_ITER_KEY, 100, APC_LIST_ACTIVE);
        $usernames = array();
        $limit = time() - $this->timeout;
        foreach($it as $i) {
            apc_fetch($i['key']); // clear invalidated entries
            if($i['mtime'] >= $limit) {
                $usernames[] = str_replace('online.', '', $i['key']);
            }
        }

        return $usernames;
    }
}
