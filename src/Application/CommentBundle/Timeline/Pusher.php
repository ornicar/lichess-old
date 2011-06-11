<?php

namespace Application\CommentBundle\Timeline;

use Application\CommentBundle\Document\Comment;
use Bundle\LichessBundle\Timeline\AbstractPusher;

class Pusher extends AbstractPusher
{
    public function pushComment(Comment $comment)
    {
        if ($gameId = $comment->getGameId()) {
            $entry = $this->templating->render('FOSCommentBundle:Comment:timelineEntry.html.twig', array(
                'comment' => $comment,
                'game_id' => $gameId
            ));
            $this->timeline->add('comment_game', $entry, $comment->getAuthor());
        }
    }
}
