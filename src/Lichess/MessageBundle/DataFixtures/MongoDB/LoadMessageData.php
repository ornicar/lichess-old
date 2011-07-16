<?php

namespace Lichess\MessageBundle\DataFixtures\MongoDB;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Bundle\ExerciseCom\MessageBundle\Document\Message;

class LoadMessageData implements OrderedFixtureInterface, FixtureInterface, ContainerAwareInterface
{
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getOrder()
    {
        return 50;
    }

    public function load($manager)
    {
        $user1 = $this->container->get('fos_user.user_manager')->findUserByUsername('user1');
        $user2 = $this->container->get('fos_user.user_manager')->findUserByUsername('user2');
        $composer = $this->container->get('ornicar_message.composer');
        $threadManager = $this->container->get('ornicar_message.thread_manager');
        $messageManager = $this->container->get('ornicar_message.message_manager');

        $message = $composer->newThread()
            ->setSender($user1)
            ->addRecipient($user2)
            ->setSubject('data fixtures message thread 1')
            ->setBody('message 1')
            ->getMessage();
        $threadManager->saveThread($message->getThread(), false);
        $messageManager->saveMessage($message);

        $message = $composer->reply($message->getThread())
            ->setSender($user2)
            ->setBody('message 2')
            ->getMessage();
        $threadManager->saveThread($message->getThread(), false);
        $messageManager->saveMessage($message);

        $message = $composer->reply($message->getThread())
            ->setSender($user2)
            ->setBody('message 3')
            ->getMessage();
        $threadManager->saveThread($message->getThread(), false);
        $messageManager->saveMessage($message);

        $message = $composer->newThread()
            ->setSubject('data fixtures message thread 2')
            ->setSender($user2)
            ->addRecipient($user1)
            ->setBody('message 4')
            ->getMessage();
        $threadManager->saveThread($message->getThread(), false);
        $messageManager->saveMessage($message);

        $message = $composer->reply($message->getThread())
            ->setSender($user1)
            ->setBody('message 5')
            ->getMessage();
        $threadManager->saveThread($message->getThread(), false);
        $messageManager->saveMessage($message);

        $message = $composer->newThread()
            ->setSubject('data fixtures message thread 3')
            ->setSender($user1)
            ->addRecipient($user2)
            ->setBody('message 6')
            ->getMessage();
        $message->getThread()->setIsSpam(true);
        $threadManager->saveThread($message->getThread(), false);
        $messageManager->saveMessage($message);
    }
}
