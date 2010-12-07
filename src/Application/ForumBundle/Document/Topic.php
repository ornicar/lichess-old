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
     * @mongodb:ReferenceOne(targetDocument="Category")
     */
    protected $category;

    /**
     * @mongodb:ReferenceOne(targetDocument="Post")
     */
    protected $firstPost;

    /**
     * @mongodb:ReferenceOne(targetDocument="Post")
     */
    protected $lastPost;

    /**
     * Get authorName
     * @return string
     */
    public function getAuthorName()
    {
        return $this->getFirstPost()->getAuthorName();
    }
}
