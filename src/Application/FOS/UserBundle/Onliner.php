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

    public function getOnlineUsernames()
    {
        $it = new \APCIterator('user', '/^online\./', APC_ITER_MTIME | APC_ITER_KEY, 100, APC_LIST_ACTIVE);
        $usernames = array();
        $limit = time() - $this->getTimeout();
        foreach($it as $i) {
            apc_fetch($i['key']); // clear invalidated entries
            if($i['mtime'] >= $limit) {
                $usernames[] = str_replace('online.', '', $i['key']);
            }
        }

        return $usernames;
    }

    protected function getTimeout()
    {
        return 20;
    }
}
