<?php

namespace Application\FOS\UserBundle\Command;

use Symfony\Component\Console\Input;

use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Bundle\FOS\UserBundle\Model\User;

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
        if(!array_key_exists('roles', $user)) {
            $user['roles'] = array();
            if(array_key_exists('isSuperAdmin', $user)) {
                if($user['isSuperAdmin']) {
                    $user['roles'][] = User::ROLE_SUPERADMIN;
                }
                unset($user['isSuperAdmin']);
            }
        }
        if(isset($user['confirmationToken'])) {
            unset($user['confirmationToken']);
        }
    }
}
