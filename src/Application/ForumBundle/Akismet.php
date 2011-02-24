<?php

namespace Application\ForumBundle;

use Zend\Service\Akismet\Akismet as ZendAkismet;
use Symfony\Component\HttpFoundation\Request;
use Bundle\ForumBundle\Router\ForumUrlGenerator;
use Application\ForumBundle\Document\Post;
use Application\ForumBundle\Document\Topic;

class Akismet
{
    protected $request;
    protected $akismet;
    protected $urlGenerator;
    protected $enabled;

    public function __construct(Request $request, ZendAkismet $akismet, ForumUrlGenerator $urlGenerator, $enabled)
    {
        $this->request = $request;
        $this->akismet = $akismet;
        $this->urlGenerator = $urlGenerator;
        $this->enabled = (bool) $enabled;
    }

    public function isPostSpam(Post $post)
    {
        if (!$this->enabled) {
            return false;
        }
        $data = array_merge($this->getRequestData(), $this->getPostData($post));

        return $this->akismet->isSpam($data);
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
