<?php

namespace Bundle\LichessBundle\Command;

use Bundle\LichessBundle\Persistence\FilePersistence;
use Bundle\LichessBundle\Persistence\MongoDBPersistence;
use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Proves things.
 */
class MongoImportCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
            ))
            ->setName('lichess:mongo:import')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gameDir = $this->container->getParameter('lichess.persistence.dir');
        $filePersistence = new FilePersistence($gameDir);
        $mongoPersistence = new MongoDBPersistence();

        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789_';
        $nbGames = $this->getNbGames($gameDir);
        $gi = 1;
        $nbFails = 0;
        foreach(str_split($chars) as $char) {
            $files = glob($gameDir.'/'.$char.'*');
            foreach($files as $file) {
                if(!is_file($file)) continue;
                $hash = basename($file);
                $game = $filePersistence->find($hash);
                if(!$game) {
                    $output->writeLn('FAIL '.$hash);
                    $nbFails++;
                    continue;
                }
                $mongoPersistence->save($game);
                $output->writeLn(sprintf('%01.0f%% %s', 100*$gi/$nbGames, $hash));
                $gi++;
            }
            unset($files);
        }

        $output->writeLn(sprintf('%d fails, %d games in DB', $nbFails, $mongoPersistence->getNbGames()));
    }

    protected function getNbGames($dir)
    {
        $output = $this->runCommand(sprintf('ls %s | wc -l', $dir));
        return (int) $output[0];
    }

    protected function runCommand($command)
    {
        exec($command, $output, $code);
        if($code !== 0) {
            throw new \RuntimeException(sprintf('Can not run '.$command.' '.implode("\n", $output)));
        }
        return $output;
    }
}
