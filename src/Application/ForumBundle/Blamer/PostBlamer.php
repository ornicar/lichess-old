<?php

namespace Application\ForumBundle\Blamer;
use Herzult\Bundle\ForumBundle\Blamer\AbstractSecurityBlamer;
use Herzult\Bundle\ForumBundle\Blamer\BlamerInterface;
use FOS\UserBundle\Model\User;

class PostBlamer extends AbstractSecurityBlamer implements BlamerInterface
{

    public function blame($post)
    {
        if ($token = $this->security->getToken()) {
            if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                $user = $token->getUser();
                if($user instanceof User) {
                    $post->setAuthor($user);
                }
            }
        }
    }
}
