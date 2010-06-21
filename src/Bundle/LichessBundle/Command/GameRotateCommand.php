<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Framework\WebBundle\Command\Command as BaseCommand;
use Symfony\Components\Console\Input\InputArgument;
use Symfony\Components\Console\Input\InputOption;
use Symfony\Components\Console\Input\InputInterface;
use Symfony\Components\Console\Output\OutputInterface;
use Symfony\Components\Console\Output\Output;
use Symfony\Framework\WebBundle\Util\Filesystem;

/**
 * Remove old games to preserve the server inode table
 */
class GameRotateCommand extends BaseCommand
{
    protected $output;
    protected $gameDir;
    protected $socketDir;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
            ))
            ->setName('lichess:game:rotate')
        ;
    }

    protected function getMaxNbGames()
    {
        return 20000;
    }

    /**
     * @see Command
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->gameDir = $this->container['lichess.persistence.dir'];
        $this->socketDir = $this->container['kernel.root_dir'].'/cache/socket';
        $nbGames = $this->getNbGames();
        $nbSockets = $this->getNbSockets();
        $maxNbGames = $this->getMaxNbGames();

        $output->writeln(sprintf('%s sockets, %d games, %d max.', $nbSockets, $nbGames, $maxNbGames));

        if($nbGames <= $maxNbGames) {
            $output->writeln('Exit.');
            return;
        }

        $nbOldGames = $nbGames - $maxNbGames;
        $output->writeln(sprintf('Will remove %d games.', $nbOldGames));

        $gameHashes = $this->runCommand(sprintf('ls -tu %s | tail -%d', $this->gameDir, $nbOldGames));

        foreach($gameHashes as $gameHash) {
            $this->deleteGame($gameHash);
        }

        $output->writeln('Done');
        
        $nbGames = $this->getNbGames();
        $nbSockets = $this->getNbSockets();

        $output->writeln(sprintf('%s sockets, %d games.', $nbSockets, $nbGames));
    }

    protected function getNbGames()
    {
        $output = $this->runCommand(sprintf('ls %s | wc -l', $this->gameDir));
        return (int) $output[0];
    }

    protected function getNbSockets()
    {
        $output = $this->runCommand(sprintf('ls %s | wc -l', $this->socketDir));
        return (int) $output[0];
    }

    protected function deleteGame($gameHash)
    {
        $this->output->write('.');
        // remove data
        unlink($this->gameDir.'/'.$gameHash);
        // remove sockets
        shell_exec(sprintf('rm %s/%s* ', $this->socketDir, $gameHash));
    }

    protected function runCommand($command)
    {
        //$this->output->writeln($command);
        exec($command, $output, $code);
        if($code !== 0)
        {
            throw new \RuntimeException(sprintf('Can not run '.$command.' '.implode("\n", $output)));
        }
        return $output;
    }
}
