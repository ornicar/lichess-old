<?php

namespace Application\UserBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class LoadUserData implements FixtureInterface, ContainerAwareInterface
{
    protected $userManager;

    protected $nbUsers = 5;

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
            $this->userManager->updateUser($user);
        }

        $manager->flush(array('safe' => true));
    }
}
