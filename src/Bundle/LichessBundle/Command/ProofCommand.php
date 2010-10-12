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
                new InputArgument('hash', InputArgument::REQUIRED, 'The player hash'),
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
        $player = $this->findPlayer($input->getArgument('hash'));
        $opponent = $player->getOpponent();
        $output->writeLn(sprintf('Opponent hash is %s', $opponent->getFullHash()));
        $message = '--- please believe me ---';
        $player->getGame()->getRoom()->addMessage($opponent->getColor(), $message);
        $htmlMessage = \Bundle\LichessBundle\Helper\TextHelper::autoLink(htmlentities($message, ENT_COMPAT, 'UTF-8'));
        $sayEvent = array(
            'type' => 'message',
            'html' => sprintf('<li class="%s">%s</li>', $opponent->getColor(), $htmlMessage)
        );
        $player->getStack()->addEvent($sayEvent);
        $opponent->getStack()->addEvent($sayEvent);
        $this->container->getLichessPersistenceService()->save($player->getGame());
        $output->writeLn('Done.');
    }

    /**
     * Get the player for this hash 
     * 
     * @param string $hash 
     * @return Player
     */
    protected function findPlayer($hash)
    {
        $gameHash = substr($hash, 0, 6);
        $playerHash = substr($hash, 6, 10);

        $game = $this->container->getLichessPersistenceService()->find($gameHash);
        if(!$game) {
            throw new \Exception('Can\'t find game '.$gameHash);
        } 

        $player = $game->getPlayerByHash($playerHash);
        if(!$player) {
            throw new \Exception('Can\'t find player '.$playerHash);
        } 

        return $player;
    }
}
