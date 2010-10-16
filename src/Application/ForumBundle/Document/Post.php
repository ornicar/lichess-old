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
     * @validation:NotBlank
     * @validation:MinLength(3)
     * @var string
     */
    protected $authorName = null;

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
