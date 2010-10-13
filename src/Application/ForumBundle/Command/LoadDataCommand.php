<?php

namespace Application\ForumBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Application\ForumBundle\Document\Category;
use Application\ForumBundle\Document\Topic;
use Application\ForumBundle\Document\Post;

/**
 * Loads test data
 */
class LoadDataCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
            ))
            ->setName('forum:data:load')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $nbCateg = 21;
        $nbTopic = 21;
        $nbPost  = 21;
        for($iCateg = 1; $iCateg <= $nbCateg; $iCateg++) {
            $categ = new Category();
            $categ->setName('Test category number '.$iCateg);
            for($iTopic = 1; $iTopic <= $nbTopic; $iTopic++) {
                $topic = new Topic();
                $topic->setCategory($categ);
                $topic->setSubject('Test topic number '.$iTopic);
                for($iPost = 1; $iPost <= $nbPost; $iPost++) {
                    $post = new Post();
                    $post->setTopic($topic);
                    $post->setMessage(str_repeat('Test message number '.$iPost."\n", 5));
                    $dm->persist($post);
                }
                $dm->persist($topic);
            }
            $dm->persist($categ);
        }

        $dm->flush();
    }
}
