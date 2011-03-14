<?php

namespace Application\CommentBundle\Controller;

use FOS\CommentBundle\Controller\ThreadController as BaseThreadController;

use FOS\CommentBundle\Model\ThreadInterface;

class ThreadController extends BaseThreadController
{
    protected function createComment(ThreadInterface $thread)
    {
        $comment = parent::createComment($thread);
        $this->container->get('lichess_comment.authorname_persistence')->loadComment($comment);

        return $comment;
    }
}
