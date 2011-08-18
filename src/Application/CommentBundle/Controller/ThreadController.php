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

    public function showFeedAction($id)
    {
        return parent::showFeedAction('game:'.$id);
    }
}
