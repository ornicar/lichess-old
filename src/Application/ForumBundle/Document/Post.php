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
     * @validation:MaxLength(10000)
     */
    protected $message;

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

    public function setMessage($message)
    {
        $this->message = $this->processMessage($message);
    }

    protected function processMessage($message)
    {
        $message = wordwrap($message, 200);
        $message = preg_replace('#(?:http://)?lichess\.org/([\w-]{6})[\w-]{4}#si', 'http://lichess.org/$1', $message);

        return $message;
    }
}
