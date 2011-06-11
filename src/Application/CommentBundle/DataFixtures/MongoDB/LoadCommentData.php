<?php

namespace Application\CommentBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use FOS\CommentBundle\Document\Comment;
use DateTime;

class LoadCommentData implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    protected $objectManager;
    protected $commentThreadManager;
    protected $commentManager;
    protected $gameManager;

    public function getOrder()
    {
        return 5;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->objectManager  = $container->get('doctrine.odm.mongodb.document_manager');
        $this->threadManager  = $container->get('fos_comment.manager.thread');
        $this->commentManager = $container->get('fos_comment.manager.comment');
        $this->gameManager    = $container->get('lichess.repository.game');
    }

    public function load($manager)
    {
        //game comments

        $gameId = $this->gameManager->findOneBy(array())->getId();

        $gameThread = $this->threadManager->createThread();
        $gameThread->setIdentifier('game:'.$gameId);
        $this->objectManager->persist($gameThread);

        $comment1 = $this->commentManager->createComment($gameThread);
        $comment1->setBody('1 - First comment in root');
        $comment1->setCreatedAt(new DateTime('-10 day'));
        $this->commentManager->addComment($comment1);
        $this->objectManager->flush();

        $comment2 = $this->commentManager->createComment($gameThread);
        $comment2->setBody('2 - Second comment in root');
        $comment2->setCreatedAt(new DateTime('-9 day'));
        $this->commentManager->addComment($comment2);
        $this->objectManager->flush();

        $comment3 = $this->commentManager->createComment($gameThread);
        $comment3->setBody('3 - First comment in comment 2');
        $comment3->setCreatedAt(new DateTime('-8 day'));
        $this->commentManager->addComment($comment3, $comment2);
        $this->objectManager->flush();

        // home comments

        $homepageThread = $this->threadManager->createThread();
        $homepageThread->setIdentifier('homepage');
        $this->objectManager->persist($homepageThread);

        /**
         * 1
         *  6
         * 2
         *  3
         *   5
         *  4
         */
        $comment1 = $this->commentManager->createComment($homepageThread);
        $comment1->setBody('1 - First comment in root');
        $comment1->setCreatedAt(new DateTime('-7 day'));
        $this->commentManager->addComment($comment1);
        $this->objectManager->flush();

        $comment2 = $this->commentManager->createComment($homepageThread);
        $comment2->setBody('2 - Second comment in root');
        $comment2->setCreatedAt(new DateTime('-6 day'));
        $this->commentManager->addComment($comment2);
        $this->objectManager->flush();

        $comment3 = $this->commentManager->createComment($homepageThread);
        $comment3->setBody('3 - First comment in comment 2');
        $comment3->setCreatedAt(new DateTime('-5 day'));
        $this->commentManager->addComment($comment3, $comment2);
        $this->objectManager->flush();

        $comment4 = $this->commentManager->createComment($homepageThread);
        $comment4->setBody('4 - Second comment in comment 2');
        $comment4->setCreatedAt(new DateTime('-4 day'));
        $this->commentManager->addComment($comment4, $comment2);
        $this->objectManager->flush();

        $comment5 = $this->commentManager->createComment($homepageThread);
        $comment5->setBody('5 - First comment in comment 3');
        $comment5->setCreatedAt(new DateTime('-3 day'));
        $this->commentManager->addComment($comment5, $comment3);
        $this->objectManager->flush();

        $comment6 = $this->commentManager->createComment($homepageThread);
        $comment6->setBody('6 - First comment in comment 1');
        $comment6->setCreatedAt(new DateTime('-2 day'));
        $this->commentManager->addComment($comment6, $comment1);
        $this->objectManager->flush();

        // Empty thread

        $articleThread = $this->threadManager->createThread();
        $articleThread->setIdentifier('article:23');
        $this->objectManager->persist($articleThread);

        $this->objectManager->flush();
    }
}
