<?php

namespace Application\ForumBundle\Blamer;
use Bundle\ForumBundle\Blamer\AbstractSecurityBlamer;
use Bundle\ForumBundle\Blamer\BlamerInterface;
use FOS\UserBundle\Model\User;

class PostBlamer extends AbstractSecurityBlamer implements BlamerInterface
{
    public function blame($post)
    {
        if ($this->security->vote('IS_AUTHENTICATED_FULLY')) {
            $user = $this->security->getToken()->getUser();
            if($user instanceof User) {
                $post->setAuthor($user);
            }
        }
    }
}
