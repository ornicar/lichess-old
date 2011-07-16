<?php

namespace Lichess\MessageBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class MigrateCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('lichess:message:migrate')
            ->setDescription('Migrates messages to new collection')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $manager->getRepository('Lichess\MessageBundle\Document\Message')->createQueryBuilder()->remove()->getQuery()->execute();
        $manager->getRepository('Lichess\MessageBundle\Document\Thread')->createQueryBuilder()->remove()->getQuery()->execute();
        $db = $manager->getDocumentDatabase('Lichess\MessageBundle\Document\Message')->getMongoDB();
        $messagesData = $db->selectCollection('message')->find();
        $composer = $this->getContainer()->get('ornicar_message.composer');
        $messageSender = $this->getContainer()->get('ornicar_message.sender');
        $userRepository = $this->getContainer()->get('fos_user.repository.user');
        $threadManager = $this->getContainer()->get('ornicar_message.thread_manager');
        $messageManager = $this->getContainer()->get('ornicar_message.message_manager');

        $count = $count = $messagesData->count();
        $output->writeln(sprintf('%d messages to migrate', $count));

        $it = 0;
        foreach ($messagesData as $data) {
            if (!$data['from']['$id'] instanceof \MongoId) continue;
            if (!$data['to']['$id'] instanceof \MongoId) continue;
            $sender = $userRepository->find($data['from']['$id']->__toString());
            $recipient = $userRepository->find($data['to']['$id']->__toString());
            if (!$sender || !$recipient) continue;
            $message = $composer->newThread()
                ->setSender($sender)
                ->addRecipient($recipient)
                ->setSubject(isset($data['subject']) ? $data['subject'] : 'No subject')
                ->setBody(isset($data['body']) ? $data['body'] : '')
                ->getMessage();
            $thread = $message->getThread();
            $date = new \DateTime();
            $date->setTimestamp($data['createdAt']->sec);
            $message->setCreatedAt($date);
            if ($data['isRead']) {
                $thread->setIsReadByParticipant($recipient, true);
            }
            $threadManager->saveThread($thread, false);
            $messageManager->saveMessage($message);
            print '.';
            if (!(++$it%50)) {
                print "$it/$count\n";
                $manager->clear();
            }
        }
    }
}
