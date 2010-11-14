<?php

namespace Application\ForumBundle\Blamer;
use Bundle\ForumBundle\Blamer\AbstractSecurityBlamer;

class PostBlamer extends AbstractSecurityBlamer implements BlamerInterface
{
    public function blame($post)
    {
        if($user = $this->security->getUser()) {
            $post->setAuthor($user);
        }
    }
}
