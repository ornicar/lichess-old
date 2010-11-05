<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Proves things.
 */
class ProofCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('id', InputArgument::REQUIRED, 'The player id'),
            ))
            ->setName('lichess:proof')
        ;
    }

    /**
     * @see Command
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        if(8 === strlen($id)) {
            $game = $this->container->getLichessPersistenceService()->find($id);
            if(!$game) {
                throw new \Exception('Can\'t find game '.$id);
            }
            foreach(array('white', 'black') as $color) {
                $output->writeLn(sprintf('%s: %s', $color, $game->getPlayer($color)->getFullId()));
            }
        }
        else {
            $player = $this->findPlayer($input->getArgument('id'));
            $opponent = $player->getOpponent();
            $output->writeLn(sprintf('Opponent id is %s', $opponent->getFullId()));
            $message = '--- please believe me ---';
            $player->getGame()->getRoom()->addMessage($opponent->getColor(), $message);
            $htmlMessage = \Bundle\LichessBundle\Helper\TextHelper::autoLink(htmlentities($message, ENT_COMPAT, 'UTF-8'));
            $sayEvent = array(
                'type' => 'message',
                'html' => sprintf('<li class="%s">%s</li>', $opponent->getColor(), $htmlMessage)
            );
            $player->addEventToStack($sayEvent);
            $opponent->addEventToStack($sayEvent);
            $this->container->getLichessPersistenceService()->save($player->getGame());
        }
        $output->writeLn('Done.');
    }

    /**
     * Get the player for this id
     *
     * @param string $id
     * @return Player
     */
    protected function findPlayer($id)
    {
        $gameId = substr($id, 0, 8);
        $playerId = substr($id, 8, 12);

        $game = $this->container->getLichessPersistenceService()->find($gameId);
        if(!$game) {
            throw new \Exception('Can\'t find game '.$gameId);
        }

        $player = $game->getPlayerById($playerId);
        if(!$player) {
            throw new \Exception('Can\'t find player '.$playerId);
        }

        return $player;
    }
}
