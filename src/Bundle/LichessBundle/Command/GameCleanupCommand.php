<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Remove games that don't have really started and are old
 */
class GameCleanupCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
            ))
            ->addOption('execute', null, InputOption::VALUE_NONE, 'Execute game removal')
            ->setName('lichess:game:cleanup')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $this->getContainer()->get('lichess.repository.game');
        $batchSize = 1000;
        $sleep = 5;

        do {
            try {
                $ids = $repo->findCandidatesToCleanup($batchSize);
                $nb = count($ids);

                $output->writeLn(sprintf('Found %d games of %d to remove', $nb, $repo->createQueryBuilder()->getQuery()->count()));

                if ($nb == 0 || !$input->getOption('execute')) {
                    return;
                }

                $output->writeLn(sprintf('Removing %d games...', $nb));
                $repo->removeByIds($ids);

                $output->writeLn('Sleep '.$sleep.' seconds');
                sleep($sleep);
            } catch (\MongoCursorTimeoutException $e) {
                $output->writeLn('<error>Time out, sleeping 20 seconds</error>');
            }
        } while ($nb > 0);
    }
}
