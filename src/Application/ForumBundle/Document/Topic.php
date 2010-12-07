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
     * @mongodb:ReferenceOne(targetDocument="Application\ForumBundle\Document\Category")
     */
    protected $category;

    /**
     * @mongodb:ReferenceOne(targetDocument="Application\ForumBundle\Document\Post")
     */
    protected $firstPost;

    /**
     * @mongodb:ReferenceOne(targetDocument="Application\ForumBundle\Document\Post")
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
