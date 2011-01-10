<?php

namespace Application\LichessMessageBundle\Command;

use Symfony\Component\Console\Input;

use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Application\LichessMessageBundle\Document\Message;

/**
 * Add some dumb messages
 */
class LoadDataCommand extends Command
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('messages:load-data')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $this->container->get('ornicar_message.repository.message');
        $user = $this->container->get('fos_user.repository.user')->findOneByUsername('thibault');
        $messenger = $this->container->get('ornicar_message.messenger');
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        for($i = 0; $i < 35; $i++) {
            $message = new Message();
            $message->setFrom($user);
            $message->setTo($user);
            $message->setSubject('The subject of the message number '.$i);
            $message->setBody(str_repeat('The body of the message number '.$i."\n", 10));
            $messenger->send($message);
        }

        $dm->flush();
        $output->writeLn('Done');
    }
}
