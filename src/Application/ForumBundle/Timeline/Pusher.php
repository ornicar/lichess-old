<?php

namespace Application\ForumBundle\Timeline;

use Application\ForumBundle\Document\Post;
use Bundle\LichessBundle\Timeline\AbstractPusher;

class Pusher extends AbstractPusher
{
    public function pushPost(Post $post)
    {
        $entry = $this->templating->render('ForumBundle:Post:timelineEntry.html.twig', array(
            'post' => $post
        ));
        $this->timeline->add('forum_post', $entry, $post->getAuthor());
    }
}
