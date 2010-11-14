<?php

namespace Application\ForumBundle\Blamer;
use Bundle\ForumBundle\Blamer\AbstractSecurityBlamer;
use Bundle\ForumBundle\Blamer\BlamerInterface;
use Symfony\Component\Security\User\AdvancedAccountInterface;

class PostBlamer extends AbstractSecurityBlamer implements BlamerInterface
{
    public function blame($post)
    {
        $user = $this->securityContext->getUser();
        if($user instanceof AdvancedAccountInterface && $user->hasRole('IS_AUTHENTICATED_FULLY')) {
            $post->setAuthor($user);
        }
    }
}
