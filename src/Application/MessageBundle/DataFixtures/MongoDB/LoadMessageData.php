<?php

namespace Application\MessageBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Application\MessageBundle\Document\Message;

class LoadMessageData implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    protected $objectManager;
    protected $userManager;
    protected $messenger;

    public function getOrder()
    {
        return 2;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->objectManager = $container->get('ornicar_message.object_manager');
        $this->userManager = $container->get('fos_user.user_manager');
        $this->messenger = $container->get('ornicar_message.messenger');
    }

    public function load($manager)
    {
        $user1 = $this->userManager->findUserByUsername('user1');
        $user2 = $this->userManager->findUserByUsername('user2');

        $this->loadMessage('Hi user2, what\'s up?', $user1, $user2);
        $this->loadMessage('I\'m doing fine, dear user1', $user2, $user1);
        $this->loadMessage('The fridge is far away, but I can do it', $user1, $user2);
        $this->loadMessage('Bring me back a beer!', $user2, $user1);
        $this->loadMessage('Re: Bring me back a beer!', $user1, $user2);

        $this->objectManager->flush();
    }

    protected function loadMessage($subject, $from, $to)
    {
        $message = new Message();
        $message->setSubject($subject);
        $message->setBody(str_repeat('Hi there, I have a thousand things to tell you', 10));
        $message->setFrom($from);
        $message->setTo($to);
        $this->messenger->send($message);
    }
}
