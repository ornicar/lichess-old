<?php

namespace Application\ForumBundle\Templating\Helper;
use Bundle\ForumBundle\Templating\Helper\ForumHelper as BaseForumHelper;
use Bundle\ForumBundle\Model\Topic;

class ForumHelper extends BaseForumHelper
{
    public function urlForTopicReply(Topic $topic, $absolute = false)
    {
        return sprintf('%s?page=%d#reply', $this->urlForTopic($topic, $absolute), $this->getTopicNumPages($topic));
    }
}
