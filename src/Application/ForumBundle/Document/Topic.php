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

    /**
     * Hack to fix temporary Form issue
     *
     * @param string $message
     * @return void
     */
    public function setMessage($message)
    {
        $this->getFirstPost()->setMessage($message);
    }

    /**
     * Hack to fix temporary Form issue
     *
     * @param string $authorName
     * @return void
     */
    public function setAuthorName($authorName)
    {
        $this->getFirstPost()->setAuthorName($authorName);
    }

    /**
     * Hack to fix temporary Form issue
     *
     * @param string $trap
     * @return void
     */
    public function setTrap($trap)
    {
        $this->getFirstPost()->setTrap($trap);
    }
}
