<?php

namespace Application\UserBundle\Command;
use Symfony\Component\Console\Input;

use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use FOS\UserBundle\Model\User;
use FOS\UserBundle\Util\Canonicalizer;

/**
 * Migrate user db to latest johanness changes
 */
class MigrateUserCommand extends Command
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('fos:user:migrate')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $this->container->get('fos_user.repository.user');
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $collection = $dm->getDocumentCollection($repo->getDocumentName())->getMongoCollection();
        $users = $collection->find();

        foreach($users as $user) {
            $output->writeLn(sprintf('Update %s', $user['username']));
            $this->migrate($user);
            $collection->update(array('_id' => $user['_id']), $user, array('safe' => true));
        }
        $output->writeLn('Done');
    }

    protected function migrate(&$user)
    {
        if(array_key_exists('isActive', $user)) {
            $user['enabled'] = (bool) $user['isActive'];
            unset($user['isActive']);
        }
        if(!array_key_exists('roles', $user)) {
            $user['roles'] = array();
        }
        if(isset($user['confirmationToken'])) {
            unset($user['confirmationToken']);
        }
        if(isset($user['usernameLower'])) {
            $canonicalizer = new Canonicalizer();
            $user['usernameCanonical'] = $canonicalizer->canonicalize($user['usernameLower']);
            $user['emailCanonical'] = $canonicalizer->canonicalize($user['email']);
            unset($user['usernameLower']);
        }
        if($user['usernameCanonical'] === 'thibault') {
            $user['roles'] = array(User::ROLE_SUPERADMIN);
        }
    }
}
