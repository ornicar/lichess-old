<?php

namespace Application\ForumBundle\Entity;
use Bundle\ForumBundle\Entity\Topic as BaseTopic;

/**
 * @orm:Entity(repositoryClass="Bundle\ForumBundle\Entity\TopicRepository")
 * @orm:Table(name="forum_topic")
 * @orm:HasLifecycleCallbacks
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
