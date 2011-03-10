<?php

namespace Application\ForumBundle\Blamer;
use Bundle\ForumBundle\Blamer\AbstractSecurityBlamer;
use Bundle\ForumBundle\Blamer\BlamerInterface;
use FOS\UserBundle\Model\User;

class PostBlamer extends AbstractSecurityBlamer implements BlamerInterface
{
    public function blame($post)
    {
        if ($token = $this->securityContext->getToken()) {
            if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
                $user = $token->getUser();
                if($user instanceof User) {
                    $post->setUser($user);
                }
            }
        }
    }
}
