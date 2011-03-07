<?php

namespace Application\ForumBundle;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\SecurityContext;
use Application\ForumBundle\Document\Topic;
use Application\ForumBundle\Document\Post;
use DateTime;

class AuthorNamePersistence
{
    protected $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function persistTopic(Topic $topic, Response $response)
    {
        if(!$this->securityContext->vote('IS_AUTHENTICATED_FULLY')) {
            $response->headers->setCookie(new Cookie(
                'lichess_forum_authorName',
                urlencode($topic->getLastPost()->getAuthorName()),
                15552000
            ));
        }
    }

    public function persistPost(Post $post, Response $response)
    {
        if(!$this->securityContext->vote('IS_AUTHENTICATED_FULLY')) {
            $response->headers->setCookie(new Cookie(
                'lichess_forum_authorName',
                urlencode($post->getAuthorName()),
                15552000
            ));
        }
    }
}
