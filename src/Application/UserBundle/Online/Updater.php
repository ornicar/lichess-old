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
                $repoUsernames[] = $user->getUsername();
            } else {
                $user->setIsOnline(false);
            }
        }
        foreach($onlineUsernames as $username) {
            if(!in_array($username, $repoUsernames)) {
                $user = $this->userRepository->findOneByUsername($username);
                $user->setIsOnline(true);
            }
        }
        $this->objectManager->flush();
    }
}
