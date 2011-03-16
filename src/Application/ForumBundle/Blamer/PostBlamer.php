<?php

namespace Application\ForumBundle\Blamer;
use Bundle\ForumBundle\Blamer\AbstractSecurityBlamer;
use Bundle\ForumBundle\Blamer\BlamerInterface;
use FOS\UserBundle\Model\User;

class PostBlamer extends AbstractSecurityBlamer implements BlamerInterface
{
    protected $timelinePusher;

    public function setTimelinePusher(Pusher $timelinePusher)
    {
        $this->timelinePusher = $timelinePusher;
    }

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
        $this->timelinePusher->pushPost($post);
    }
}
