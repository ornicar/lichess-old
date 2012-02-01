<?php

namespace Application\UserBundle\Command;
use Symfony\Component\Console\Input;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Bundle\LichessBundle\Document\Game;

class MigrateUserCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('lichess:user:migrate')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gameRepo = $this->getContainer()->get('lichess.repository.game');
        $userRepo = $this->getContainer()->get('fos_user.repository.user');
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $userCollection = $dm->getDocumentCollection($userRepo->getDocumentName())->getMongoCollection();

        $users = iterator_to_array($userCollection->find(array(), array('_id' => true, 'username' => true)));

        $it = 1;
        foreach($users as $user) {
            $builder = $gameRepo->createByUserIdQuery($user['_id'])->field('status')->gte(Game::MATE);
            $output->writeLn(sprintf('%d %s', $it, $user['username']));
            $userCollection->update(
                array('_id' => $user['_id']),
                array('$set' => array(
                    'nbGames' => $builder->getQuery()->count(),
                    'nbRatedGames' => $builder->field('isRated')->equals(true)->getQuery()->count()
                ))
            );
            $it++;
        }
        $output->writeLn('Done');
    }
}
