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
        $sleep = 10;

        do {
            try {
                $ids = $repo->findCandidatesToCleanup($batchSize);
                $nb = count($ids);

                if ($nb == 0) {
                    return;
                }

                $output->writeLn(sprintf('Removing %d games...', $nb));
                $repo->removeByIds($ids);

                if ($nb == $batchSize) {
                    $output->writeLn('Sleep '.$sleep.' seconds');
                    sleep($sleep);
                }
            } catch (\MongoCursorTimeoutException $e) {
                $output->writeLn('<error>Time out, sleeping 60 seconds</error>');
                sleep(60);
            }
        } while ($nb > 0);
    }
}
