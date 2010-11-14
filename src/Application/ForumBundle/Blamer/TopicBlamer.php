<?php

namespace Bundle\ForumBundle\Blamer;
use Bundle\ForumBundle\Blamer\AbstractSecurityBlamer;

class TopicBlamer extends AbstractSecurityBlamer implements BlamerInterface
{
    public function blame($topic)
    {
        if($user = $this->security->getUser()) {
            $topic->setAuthor($topic);
        }
    }
}
