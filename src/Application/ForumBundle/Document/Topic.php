<?php

namespace Application\ForumBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Herzult\Bundle\ForumBundle\Document\Topic as BaseTopic;

/**
 * @MongoDB\Document(
 *   repositoryClass="Herzult\Bundle\ForumBundle\Document\TopicRepository",
 *   collection="forum_topic"
 * )
 */
class Topic extends BaseTopic
{
    /**
     * @MongoDB\ReferenceOne(targetDocument="Application\ForumBundle\Document\Category")
     */
    protected $category;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Application\ForumBundle\Document\Post")
     */
    protected $firstPost;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Application\ForumBundle\Document\Post")
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
}
