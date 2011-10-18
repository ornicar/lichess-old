<?php

namespace Application\ForumBundle;

use Ornicar\AkismetBundle\Akismet\AkismetInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Herzult\Bundle\ForumBundle\Router\ForumUrlGenerator;
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
        return $this->isSpamLike($post) || $this->akismet->isSpam($this->getPostData($post));
    }

    public function isTopicSpam(Topic $topic)
    {
        return $this->isSpamLike($topic->getFirstPost()) || $this->akismet->isSpam($this->getTopicData($topic));
    }

    protected function isSpamLike(Post $post)
    {
        $hasBr = strpos(strtolower($post->getMessage()), '<br') !== false;
        $hasHref = strpos(strtolower($post->getMessage()), '<a href') !== false;

        return $hasBr || $hasHref;
    }

    protected function getPostData(Post $post)
    {
        return array(
            'comment_type'    => 'comment',
            'comment_author'  => $post->getAuthorName(),
            'comment_content' => $post->getMessage()
        );
    }

    protected function getTopicData(Topic $topic)
    {
        return array(
            'comment_type'    => 'comment',
            'comment_author'  => $topic->getFirstPost()->getAuthorName(),
            'comment_content' => $topic->getSubject().' '.$topic->getFirstPost()->getMessage()
        );
    }
}
