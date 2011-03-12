<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

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
            ->setName('lichess:game:fix')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->repo = $this->container->get('lichess.repository.game');

        $this->fixUnFinishedGames();
    }

    protected function fixUnFinishedGames()
    {
        $date = new \DateTime('-1 day');
        $games = $this->repo->createQueryBuilder()
            ->field('status')->equals(Game::STARTED)
            ->field('clock')->exists(true)
            ->field('updatedAt')->lt(new \MongoDate($date->getTimestamp()))
            ->getQuery()->execute();

        $output->writeLn(sprintf('Found %d unfinished games', $games->count()));

        foreach ($games as $game) {
            $output->writeLn(sprintf('Finish %s', $this->generateUrl($game->getId())));
        }
    }

    protected function generateUrl($path)
    {
        return sprintf('http://lichess.org/%s', $path);
    }
}
