<?php

namespace Application\DoctrineUserBundle\Command;

use Symfony\Component\Console\Input;

use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Bundle\DoctrineUserBundle\Model\User;

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
            ->setName('doctrine:user:migrate')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $this->container->get('doctrine_user.repository.user');
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $collection = $dm->getDocumentCollection($repo->getObjectClass())->getMongoCollection();
        $users = $collection->find();

        foreach($users as $user) {
            $output->writeLn(sprintf('Update %s', $user['username']));
            $this->migrate($user);
            $collection->update(array('_id' => $user['_id']), $user);
        }
        $output->writeLn('Done');
    }

    protected function migrate(&$user)
    {
        if(array_key_exists('isActive', $user)) {
            $user['enabled'] = (bool) $user['isActive'];
            unset($user['isActive']);
        }
        $user['roles'] = array();
        if(array_key_exists('isSuperAdmin', $user) && $user['isSuperAdmin']) {
            $user['roles'][] = User::ROLE_SUPERADMIN;
            unset($user['isSuperAdmin']);
        }
    }
}
