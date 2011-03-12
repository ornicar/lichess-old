<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Give infos about the game
 */
class GameDebugCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('id', InputArgument::REQUIRED, 'Public ID of a game'),
            ))
            ->setName('lichess:game:debug')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $this->container->get('lichess.repository.game');
        $game = $repo->findOneById($input->getArgument('id'));
        if (!$game) {
            throw new \InvalidArgumentException('No game found.');
        }
        $output->writeLn(sprintf('Game   %s', $this->generateUrl($game->getId())));
        $output->writeLn(sprintf('White  %s', $this->generateUrl($game->getPlayer('white')->getFullId())));
        $output->writeLn(sprintf('Black  %s', $this->generateUrl($game->getPlayer('black')->getFullId())));
    }

    protected function generateUrl($path)
    {
        return sprintf('http://lichess.org/%s', $path);
    }
}
