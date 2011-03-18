<?php

namespace Application\CommentBundle\Controller;

use FOS\CommentBundle\Controller\ThreadController as BaseThreadController;
use FOS\CommentBundle\Model\ThreadInterface;
use Bundle\LichessBundle\Document\Game;

class ThreadController extends BaseThreadController
{
    protected function createComment(ThreadInterface $thread)
    {
        $comment = parent::createComment($thread);
        $this->container->get('lichess_comment.authorname_persistence')->loadComment($comment);

        return $comment;
    }

    public function showGameAction(Game $game)
    {
        $identifier = 'game:'.$game->getId();
        $thread = $this->container->get('fos_comment.manager.thread')->findThreadByIdentifier($identifier);
        if (!$thread) {
            $thread = $this->container->get('fos_comment.creator.thread')->create($identifier);
        }

        $comment = $this->createComment($thread);
        $form = $this->container->get('fos_comment.form_factory.comment')->createForm();
        $form->setData($comment);

        return $this->container->get('templating')->renderResponse('LichessCommentBundle:Thread:showGame.html.twig', array(
            'game'   => $game,
            'thread' => $thread,
            'form'   => $form
        ));
    }

    public function showFeedAction($identifier)
    {
        return parent::showFeedAction('game:'.$identifier);
    }
}
