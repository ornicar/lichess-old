<?php

namespace Application\ForumBundle\Document;
use Bundle\ForumBundle\Document\Category as BaseCategory;

/**
 * @mongodb:Document(
 *   repositoryClass="Bundle\ForumBundle\Document\CategoryRepository",
 *   collection="forum_category"
 * )
 * @mongodb:HasLifecycleCallbacks
 */
class Category extends BaseCategory
{
    /**
     * @mongodb:ReferenceOne(targetDocument="Application\ForumBundle\Document\Topic")
     */
    protected $lastTopic;

    /**
     * @mongodb:ReferenceOne(targetDocument="Application\ForumBundle\Document\Post")
     */
    protected $lastPost;
}
