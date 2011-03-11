<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Bundle\LichessBundle\Document\History;

/**
 * Create the History documents from user documents
 */
class BuildHistoryCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('lichess:history:build')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->container->get('lichess.object_manager');

        $historyRepository = $this->container->get('lichess.repository.history');
        $historyRepository->createQueryBuilder()->remove()->getQuery()->execute();

        $userRepository = $this->container->get('fos_user.repository.user');
        $userArrays = $userRepository
            ->createQueryBuilder()
            ->select('_id', 'eloHistory')
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        foreach ($userArrays as $userArray) {
            $user = $userRepository->findOneBy(array('_id' => $userArray['_id']));
            $history = new History($user);
            $oldHistory = $userArray['eloHistory'];
            ksort($oldHistory);
            foreach ($oldHistory as $timestamp => $elo) {
                if (!$history->hasEntry($timestamp)) {
                    $history->addUnknownGame($timestamp, $elo);
                }
            }
            $dm->persist($history);
        }
        $dm->flush();
    }
}
