<?php

namespace Application\ForumBundle\Blamer;
use Bundle\ForumBundle\Blamer\AbstractSecurityBlamer;
use Bundle\ForumBundle\Blamer\BlamerInterface;

class TopicBlamer extends AbstractSecurityBlamer implements BlamerInterface
{
    public function blame($topic)
    {
        if($user = $this->security->getUser()) {
            $topic->setAuthor($topic);
        }
    }
}
