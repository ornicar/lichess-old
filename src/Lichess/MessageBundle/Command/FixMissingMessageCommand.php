<?php

namespace Lichess\MessageBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class FixMissingMessageCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('lichess:message:fix-missing-message')
            ->setDescription('Remove references to messages that do not exist')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $threadClass = 'Lichess\MessageBundle\Document\Thread';
        $manager = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $db = $manager->getDocumentDatabase($threadClass)->getMongoDB();
        $messageCollection = $db->selectCollection('message_message');
        $threadCollection = $db->selectCollection('message_thread');
        $threads = iterator_to_array($threadCollection->find());

        $output->writeln(sprintf('%d threads to check', count($threads)));
        foreach ($threads as $thread) {
            $messages = $thread['messages'];
            $validMessages = array_values(iterator_to_array($messageCollection->find(array('thread.$id' => $thread['_id']))));
            if (count($messages) != count($validMessages)) {
                $refs = array_map(function (array $message) {
                    return array('$ref' => 'message_message', '$id' => $message['_id']);
                }, $validMessages);
                $output->writeLn('Fix thread '.$thread['_id']->__toString().' '.$thread['subject']);
                $output->writeLn(var_export($refs, true));
                $threadCollection->update(
                    array('_id' => $thread['_id']),
                    array('$set' => array('messages' => $refs))
                );
            }
        }
        $output->writeln('Done');
    }
}
