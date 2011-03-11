<?php

namespace Application\UserBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use FOS\UserBundle\Model\User;

class LoadUserData implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    protected $userManager;

    protected $nbUsers = 5;

    public function getOrder()
    {
        return 0;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->userManager = $container->get('fos_user.user_manager');
    }

    public function load($manager)
    {
        for ($it = 1; $it <= $this->nbUsers; $it++) {
            $user = $this->userManager->createUser();
            $user->setUsername('user'.$it);
            $user->setEmail('user'.$it.'@site.org');
            $user->setPlainPassword('password'.$it);
            $user->setEnabled(true);
            $user->setBio('Here I describe myself and say philosophical bullshits, because I am '.$user->getUsername());
            $this->userManager->updateUser($user);
        }

        $user = $this->userManager->createUser();
        $user->setUsername('thib');
        $user->setEmail('thibault.duplessis@gmail.com');
        $user->setPlainPassword('pass');
        $user->setEnabled(true);
        $user->setBio('Here I describe myself and say philosophical bullshits, because I am '.$user->getUsername());
        $user->addRole(User::ROLE_SUPERADMIN);
        $this->userManager->updateUser($user);
    }
}
