<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
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
        $dm = $this->container->get('lichess.object_manager');
        $tm = $this->container->get('lichess.translation.manager');
        foreach($tm->getAvailableLanguages() as $code => $name) {
            if("en" === $code) {
                continue;
            }
            $messages = $tm->getMessages($code);
            $messages = $tm->sortMessages($messages);
            $tm->saveMessages($code, $messages);
        }
    }
}
