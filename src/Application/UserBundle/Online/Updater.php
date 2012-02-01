<?php

namespace Application\UserBundle\Online;

use Application\UserBundle\Document\UserRepository;

class Updater
{
    protected $objectManager;
    protected $userRepository;
    protected $cache;

    public function __construct($objectManager, UserRepository $userRepository, Cache $cache)
    {
        $this->objectManager  = $objectManager;
        $this->userRepository = $userRepository;
        $this->cache          = $cache;
    }

    public function update()
    {
        $onlineUsernames = $this->cache->getOnlineUsernames();
        $repoUsernames = array();
        foreach($this->userRepository->findOnlineUsers() as $user) {
            if(in_array($user->getUsername(), $onlineUsernames)) {
                $repoUsernames[] = $user->getUsernameCanonical();
            } else {
                $user->setIsOnline(false);
            }
        }
        $newOnlineUsers = $this->userRepository->findByUsernameCanonicals(
            array_diff($onlineUsernames, $repoUsernames)
        );
        foreach($newOnlineUsers as $newOnlineUser) {
            $newOnlineUser->setIsOnline(true);
        }
    }
}
