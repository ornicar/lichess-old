<?php

namespace Bundle\LichessBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class LoadTimelineEntryFixtures implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    protected $gamePusher;
    protected $commentPusher;
    protected $games;
    protected $comments;

    public function getOrder()
    {
        return 4;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->gamePusher    = $container->get('lichess.timeline.pusher');
        $this->commentPusher = $container->get('lichess_comment.timeline.pusher');
        $this->forumPusher   = $container->get('lichess_forum.timeline.pusher');
        $this->games         = $container->get('lichess.repository.game')->findAll();
        $this->comments      = $container->get('doctrine.odm.mongodb.document_manager')
            ->getRepository($container->getParameter('fos_comment.model.comment.class'))
            ->findAll();
        $this->posts         = $container->get('doctrine.odm.mongodb.document_manager')
            ->getRepository($container->getParameter('forum.model.post.class'))
            ->findAll();
    }

    public function load($manager)
    {
        foreach ($this->games as $game) {
            $game->setWinner($game->getCreator());
            $this->gamePusher->pushMate($game);
        }

        foreach ($this->comments as $comment) {
            $this->commentPusher->pushComment($comment);
        }

        foreach ($this->posts as $comment) {
            $this->forumPusher->pushPost($comment);
        }

        $manager->flush();
    }
}
