<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Chess\FinisherException;

/**
 * Fix games that reached an anormal state
 */
class GameFixCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
            ))
            ->addOption('execute', null, InputOption::VALUE_NONE, 'Execute game finish')
            ->setName('lichess:game:fix')
            ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $this->container->get('lichess.repository.game');
        $games = $this->repo->findCandidatesToFinish();
        $nb = $games->count();

        $output->writeLn(sprintf('Found %d unfinished games', $nb));

        if($input->getOption('execute') && $nb) {
            $finisher = $this->container->get('lichess.finisher');
            foreach ($games as $game) {
                if (!$game->hasClock()) {
                    continue;
                }
                $output->writeLn(sprintf('Finish %s', $this->generateUrl($game->getId())));
                try {
                    $finisher->outoftime($game->getCreator());
                } catch (FinisherException $e) {
                    $output->writeLn($e->getMessage());
                }
            }
            $dm = $this->container->get('lichess.object_manager');
            $dm->flush();
        }
    }

    protected function generateUrl($path)
    {
        return sprintf('http://lichess.org/%s', $path);
    }
}
