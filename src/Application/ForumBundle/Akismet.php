<?php

namespace Application\ForumBundle;

use Zend\Service\Akismet\Akismet as ZendAkismet;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Bundle\ForumBundle\Router\ForumUrlGenerator;
use Application\ForumBundle\Document\Post;
use Application\ForumBundle\Document\Topic;
use Zend\Service\Akismet\Exception as AkismetException;

class Akismet
{
    protected $request;
    protected $akismet;
    protected $urlGenerator;
    protected $securityContext;
    protected $enabled;

    public function __construct(Request $request, ZendAkismet $akismet, ForumUrlGenerator $urlGenerator, SecurityContext $securityContext, $enabled)
    {
        $this->request         = $request;
        $this->akismet         = $akismet;
        $this->urlGenerator    = $urlGenerator;
        $this->securityContext = $securityContext;
        $this->enabled         = (bool) $enabled;
    }

    public function isPostSpam(Post $post)
    {
        if (!$this->isEnabled()) {
            return $post->getAuthorName() == 'viagra-test-123';
        }
        $data = array_merge($this->getRequestData(), $this->getPostData($post));

        return $this->akismet->isSpam($data);
    }

    public function isTopicSpam(Topic $topic)
    {
        if (!$this->isEnabled()) {
            return $topic->getFirstPost()->getAuthorName() == 'viagra-test-123';
        }
        $data = array_merge($this->getRequestData(), $this->getTopicData($topic));

        return $this->isSpam($data);
    }

    protected function isEnabled()
    {
        return $this->isEnabled && !$this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }

    protected function isSpam(array $data)
    {
        try {
            return $this->akismet->isSpam($data);
        } catch (AkismetException $e) {
            return true;
        }
    }

    protected function getPostData(Post $post)
    {
        return array(
            'permalink'       => $this->urlGenerator->urlForTopic($post->getTopic(), true),
            'comment_type'    => 'comment',
            'comment_author'  => $post->getAuthorName(),
            'comment_content' => $post->getMessage()
        );
    }

    protected function getTopicData(Topic $topic)
    {
        return array(
            'permalink'       => $this->urlGenerator->urlForCategory($topic->getCategory(), true),
            'comment_type'    => 'comment',
            'comment_author'  => $topic->getFirstPost()->getAuthorName(),
            'comment_content' => $topic->getSubject().' '.$topic->getFirstPost()->getMessage()
        );
    }

    protected function getRequestData()
    {
        $server = $this->request->server;

        return array(
            'user_ip'    => $this->request->getClientIp(),
            'user_agent' => $server->get('HTTP_USER_AGENT'),
            'referrer'   => $server->get('HTTP_REFERER'),
        );
    }
}
