<?php

namespace Lichess\TranslationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Import games from previous format
 */
class FixTranslationCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
            ))
            ->setName('lichess:translation:fix')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $tm = $this->getContainer()->get('lichess_translation.manager');
        foreach($tm->getAvailableLanguages() as $code => $name) {
            print($code.', ');
            if("en" === $code) {
                continue;
            }
            $messages = $tm->getMessages($code);
            $messages = $tm->sortMessages($messages);
            $tm->saveMessages($code, $messages);
        }
        $output->writeLn('Done');
    }
}
