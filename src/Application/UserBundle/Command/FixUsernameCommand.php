<?php

namespace Application\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class FixUsernameCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->addOption('flush', null, InputOption::VALUE_NONE, 'Flushes the changes to the DB')
            ->setName('lichess:fix-usernames')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectManager = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $userRepo = $this->getContainer()->get('fos_user.repository.user');

        $users = $userRepo->findAll();

        foreach($users as $user) {
            if(preg_match('/[^\w]/', $user->getUsername())) {
                $newUsername = $this->fix($user->getUsername());
                if ($newUsername === $user->getUsername()) {
                    continue;
                }
                $newUsernameCanonical = $this->fix($user->getUsernameCanonical());
                $output->writeLn(sprintf('Modified %s to %s', $user->getUsername(), $newUsername));
                $user->setUsername($newUsername);
                $user->setUsernameCanonical($newUsernameCanonical);
            }
        }

        if ($input->getOption('flush')) {
            $output->writeln('Flushing');
            $objectManager->flush();
        } else {
            $output->writeln('NOT flushing');
        }
    }

    protected function fix($text)
    {
        $text = str_replace('-', 'arstdhneioqwfpgjluy', $text);
        $text = preg_replace('/[^A-Za-z0-9_]/', '-', $text);
        $text = str_replace('arstdhneioqwfpgjluy', '-', $text);

        return $text;
    }
}
