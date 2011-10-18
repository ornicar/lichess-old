<?php

namespace Application\ForumBundle\Twig;
use Herzult\Bundle\ForumBundle\Twig\ForumExtension as BaseForumExtension;
use Herzult\Bundle\ForumBundle\Model\Topic;

class ForumExtension extends BaseForumExtension
{
    public function urlForTopicReply(Topic $topic, $absolute = false)
    {
        return sprintf('%s?page=%d#reply', $this->urlForTopic($topic, $absolute), $this->topicNumPages($topic));
    }
}
