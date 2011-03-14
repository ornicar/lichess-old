<?php

namespace Application\CommentBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\SecurityContext;
use Application\CommentBundle\Document\Comment;
use DateTime;

class AuthorNamePersistence
{
    protected $securityContext;
    protected $request;
    protected $cookieName = 'lichess_authorName';

    public function __construct(SecurityContext $securityContext, Request $request)
    {
        $this->securityContext = $securityContext;
        $this->request = $request;
    }

    public function persistComment(Comment $comment, Response $response)
    {
        if($this->isAnonymous()) {
            $response->headers->setCookie(new Cookie(
                $this->cookieName,
                urlencode($comment->getAuthorName()),
                time() + 15552000
            ));
        }
    }

    public function loadComment(Comment $comment)
    {
        if($this->isAnonymous()) {
            if ($authorName = $this->request->cookies->get($this->cookieName)) {
                $comment->setAuthorName(urldecode($authorName));
            }
        }
    }

    protected function isAnonymous()
    {
        return !$this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }
}
