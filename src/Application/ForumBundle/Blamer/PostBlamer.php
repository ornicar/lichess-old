<?php

namespace Application\ForumBundle\Blamer;
use Bundle\ForumBundle\Blamer\AbstractSecurityBlamer;
use Bundle\ForumBundle\Blamer\BlamerInterface;
use Bundle\FOS\UserBundle\Model\User;

class PostBlamer extends AbstractSecurityBlamer implements BlamerInterface
{
    public function blame($post)
    {
        $user = $this->security->getUser();
        if($user instanceof User) {
            $post->setAuthor($user);
        }
    }
}
