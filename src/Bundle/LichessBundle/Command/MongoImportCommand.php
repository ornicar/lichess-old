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
                new InputArgument('start', InputArgument::REQUIRED, 'The starting letter'),
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
        $mongoCollection = $mongoPersistence->getCollection();

        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789_';
        $startChar = $input->getArgument('start');
        $charStartIndex = strpos($chars, $startChar);
        $nbFails = 0;
        $nbEmpty = 0;
        foreach(str_split($chars) as $charIndex => $char1) {
            if($charIndex < $charStartIndex) continue;
            $output->writeLn('Indexing...');
            $mongoPersistence->ensureIndexes();
            foreach(str_split($chars) as $char2) {
                $start = microtime(true);
                $files = glob($gameDir.'/'.$char1.$char2.'*');
                $gameArrays = array();
                foreach($files as $file) {
                    $hash = basename($file);
                    if(strlen($hash) !== 6) continue;
                    if($mongoCollection->count(array('hash' => $hash))) continue;
                    $game = $filePersistence->find($hash);
                    if(!$game) {
                        $output->writeLn('FAIL '.$hash);
                        $nbFails++;
                        continue;
                    }
                    if($game->getTurns() < 4) {
                        $nbEmpty++;
                        continue;
                    }
                    $gameArrays[] = array(
                        'bin' => $mongoPersistence->encode(serialize($game)),
                        'hash' => $hash,
                        'status' => $game->getStatus(),
                        'turns' => $game->getTurns(),
                        'upd' => filemtime($file)
                    );

                    unset($game);
                }
                if(!empty($gameArrays)) $mongoCollection->batchInsert($gameArrays);
                $time = microtime(true) - $start;
                $output->writeLn(sprintf('%s%s* %d in %01.2fs', $char1, $char2, count($files), $time));
                unset($files, $gameArrays);
                $filePersistence->clear();
            }
        }

        $output->writeLn(sprintf('%d fails, %d empty, %d games in DB', $nbFails, $nbEmpty, $mongoPersistence->getNbGames()));
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
