<?php

namespace Application\ForumBundle;

use Ornicar\AkismetBundle\Akismet\AkismetInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Bundle\ForumBundle\Router\ForumUrlGenerator;
use Application\ForumBundle\Document\Post;
use Application\ForumBundle\Document\Topic;
use Zend\Service\Akismet\Exception as AkismetException;

class Akismet
{
    protected $akismet;

    public function __construct(AkismetInterface $akismet)
    {
        $this->akismet         = $akismet;
    }

    public function isPostSpam(Post $post)
    {
        return $this->akismet->isSpam($this->getPostData($post));
    }

    public function isTopicSpam(Topic $topic)
    {
        return $this->akismet->isSpam($this->getTopicData($post));
    }
}
