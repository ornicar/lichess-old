<?php

namespace Application\ForumBundle\Document;
use Bundle\ForumBundle\Document\Topic as BaseTopic;

/**
 * @mongodb:Document(
 *   repositoryClass="Bundle\ForumBundle\Document\TopicRepository",
 *   collection="forum_topic"
 * )
 * @mongodb:HasLifecycleCallbacks
 */
class Topic extends BaseTopic
{
    /**
     * Get authorName
     * @return string
     */
    public function getAuthorName()
    {
        return $this->getFirstPost()->getAuthorName();
    }
}
