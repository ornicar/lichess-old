<?php

namespace Lichess\TranslationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpKernel\Util\Filesystem;

/**
 * Import games from previous format
 */
class DumpTranslationCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
            ))
            ->setName('lichess:translation:dump')
            ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $items = array(
            "Unlimited",
            "Rated",
            "Casual",
            "No game available right now, create one!",
            "This game is rated",
            "White creates the game",
            "Black creates the game",
            "White joins the game",
            "Black joins the game",
            "Draw offer sent",
            "Draw offer declined",
            "Draw offer accepted",
            "Draw offer canceled",
            "Game over",
            "Your turn",
            "Waiting for opponent"
        );
        $tm = $this->getContainer()->get('lichess_translation.manager');
        $translator = $this->getContainer()->get('translator');
        $fs = new Filesystem();
        $dir = $this->getContainer()->getParameter('kernel.root_dir') . '/../web/trans';
        $fs->mkdir($dir);
        foreach($tm->getAvailableLanguages() as $code => $name) {
            $translator->setLocale($code);
            $translations = array();
            foreach ($items as $item) {
                $translations[$item] = $translator->trans($item);
            }
            $content = sprintf("lichess_translations = %s;", json_encode($translations));
            $file = sprintf("%s/%s.js", $dir, $code);
            file_put_contents($file, $content);
        }
        $output->writeLn('Done');
    }
}
