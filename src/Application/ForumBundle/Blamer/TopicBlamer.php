<?php

namespace Application\ForumBundle\Blamer;
use Bundle\ForumBundle\Blamer\AbstractSecurityBlamer;
use Bundle\ForumBundle\Blamer\BlamerInterface;
use Symfony\Component\Security\User\AdvancedAccountInterface;

class TopicBlamer extends AbstractSecurityBlamer implements BlamerInterface
{
    public function blame($topic)
    {
    }
}
