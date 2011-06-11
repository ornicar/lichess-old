<?php

namespace Application\ForumBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Application\ForumBundle\Document\Category;
use Application\ForumBundle\Document\Topic;
use Application\ForumBundle\Document\Post;

/**
 * Loads initial data
 */
class InitialDataCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
            ))
            ->setName('forum:data:init')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        foreach(array('Category', 'Topic', 'Post') as $model) {
            $dm->getRepository('ForumBundle:'.$model)->createQueryBuilder()->remove()->getQuery()->execute();
        }

        $this->addCateg('General Chess Discussion', 'The place to discuss general Chess topics');
        $this->addCateg('Getting Started', 'What are the rules, how the pieces move? What are special moves like en passant? How do you draw or mate?');
        $this->addCateg('Game analysis', 'Show us your game an let the community analyse it');
        $this->addCateg('Lichess Feedback', 'Bug reports, feature requests, suggestions... Help us making lichess.org better!');
        $this->addCateg('Off-Topic Discussion', 'Everything that isn\'t related to chess');

        $dm->flush();
    }

    protected function addCateg($name, $description)
    {
        $categ = new Category();
        $categ->setName($name);
        $categ->setDescription($description);

        $topic = new Topic();
        $topic->setSubject('New forum category: '.$name);
        $topic->setCategory($categ);

        $post = new Post();
        $post->setMessage($description);
        $post->setAuthorName('lichess.org staff');
        $post->setTopic($topic);

        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $dm->persist($categ);
        $dm->persist($topic);
        $dm->persist($post);
    }
}
