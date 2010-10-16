<?php

namespace Application\ForumBundle\Document;
use Bundle\ForumBundle\Document\Post as BasePost;

/**
 * @mongodb:Document(
 *   repositoryClass="Bundle\ForumBundle\Document\PostRepository",
 *   collection="forum_post"
 * )
 * @mongodb:HasLifecycleCallbacks
 */
class Post extends BasePost
{
    /**
     * The author name
     *
     * @mongodb:String
     * @var string
     */
    protected $authorName = '';

    /**
     * Get authorName
     * @return string
     */
    public function getAuthorName()
    {
      return $this->authorName;
    }

    /**
     * Set authorName
     * @param  string
     * @return null
     */
    public function setAuthorName($authorName)
    {
      $this->authorName = $authorName;
    }
}
