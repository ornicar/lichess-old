<?php

namespace Application\ForumBundle\Blamer;
use Bundle\ForumBundle\Blamer\AbstractSecurityBlamer;
use Bundle\ForumBundle\Blamer\BlamerInterface;
use Symfony\Component\Security\User\AdvancedAccountInterface;

class TopicBlamer extends AbstractSecurityBlamer implements BlamerInterface
{
    public function blame($topic)
    {
        $user = $this->security->getUser();
        if($user instanceof AdvancedAccountInterface && $user->hasRole('IS_AUTHENTICATED_FULLY')) {
            $topic->setAuthor($user);
        }
    }
}
