<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Bundle\LichessBundle\Document\Game;

/**
 * Fix games that reached an anormal state
 */
class GameFixCommand extends BaseCommand
{
    protected $dm;
    protected $repo;

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
        $this->dm = $this->container->get('lichess.object_manager');
        $this->repo = $this->container->get('lichess.repository.game');

        $this->fixUnFinishedGames($output);

        $this->dm->flush();
    }

    protected function fixUnFinishedGames(OutputInterface $output)
    {
        $date = new \DateTime('-1 day');
        $games = $this->repo->createQueryBuilder()
            ->field('status')->equals(Game::STARTED)
            ->field('clock')->exists(true)
            ->field('clock')->notEqual(null)
            ->field('updatedAt')->lt(new \MongoDate($date->getTimestamp()))
            ->limit(1000)
            ->getQuery()->execute();

        $output->writeLn(sprintf('Found %d unfinished games', $games->count()));

        $finisher = $this->container->get('lichess.finisher');

        foreach ($games as $game) {
            if (!$game->hasClock()) {
                continue;
            }
            $output->writeLn(sprintf('Finish %s', $this->generateUrl($game->getId())));
            $finisher->outoftime($game->getCreator());
        }
    }

    protected function generateUrl($path)
    {
        return sprintf('http://lichess.org/%s', $path);
    }
}
