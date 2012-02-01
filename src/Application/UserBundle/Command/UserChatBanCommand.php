<?php

namespace Application\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use FOS\UserBundle\Model\User;

class UserChatBanCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('lichess:user:chat-ban')
            ->setDescription('Ban a user from public chat')
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
            ));
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $objectManager = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $userRepo = $this->getContainer()->get('fos_user.repository.user');
        $user = $userRepo->findOneByUsernameCanonical($username);

        $user->toggleChatBan();
        $objectManager->flush();

        $output->writeln(sprintf('User "%s" has been %s from the public chat room.', $username, $user->isChatBan() ? "banned" : "unbanned"));
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('username')) {
            $username = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a username:',
                function($username)
                {
                    if (empty($username)) {
                        throw new \Exception('Username can not be empty');
                    }
                    return $username;
                }
            );
            $input->setArgument('username', $username);
        }
    }
}
