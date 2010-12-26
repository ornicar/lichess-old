<?php

namespace Bundle\LichessBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Generate document hydrators
 */
class GenerateHydratorsCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
            ))
            ->setName('lichess:generate-hydrators')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->container->get('lichess.object_manager');

        $metadatas = $dm->getMetadataFactory()->getAllMetadata();

        $destPath = $dm->getConfiguration()->getHydratorDir();

        if ( ! is_dir($destPath)) {
            mkdir($destPath, 0777, true);
        }

        $destPath = realpath($destPath);

        if ( ! file_exists($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Hydrators destination directory '<info>%s</info>' does not exist.", $destPath)
            );
        } else if ( ! is_writable($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Hydrators destination directory '<info>%s</info>' does not have write permissions.", $destPath)
            );
        }

        if ( count($metadatas)) {
            $names = array();
            foreach ($metadatas as $index => $metadata) {
                $name = $metadata->name;
                if(!$metadata->isMappedSuperclass && !isset($names[$name])) {
                    $output->write(sprintf('Processing entity "<info>%s</info>"', $name) . PHP_EOL);
                    // Generating Hydrator
                    $dm->getHydratorFactory()->getHydratorFor($name);
                    $names[$name] = true;
                }
            }

            // Outputting information message
            $output->write(PHP_EOL . sprintf('Hydrator classes generated to "<info>%s</INFO>"', $destPath) . PHP_EOL);
        } else {
            $output->write('No Metadata Classes to process.' . PHP_EOL);
        }
    }
}
