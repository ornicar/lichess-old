<?php

namespace Application\ForumBundle\Entity;
use Bundle\ForumBundle\Entity\Post as BasePost;
use Application\DoctrineUserBundle\Entity\User;

/**
 * @orm:Entity(repositoryClass="Bundle\ForumBundle\Entity\PostRepository")
 * @orm:Table(name="forum_post")
 * @orm:HasLifecycleCallbacks
 */
class Post extends BasePost
{
    /**
     * The author name
     *
     * @orm:Column(type="string")
     * @var string
     */
    protected $authorName = '';

    /**
     * The author user if any
     *
     * @orm:ManyToOne(targetEntity="Application\DoctrineUserBundle\Entity\User")
     * @var User
     */
    protected $author = null;

    /**
     * @validation:MaxLength(10000)
     */
    protected $message;

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param  User
     * @return null
     */
    public function setAuthor(User $author)
    {
        $this->author = $author;
        $this->authorName = $author->getUsername();
    }

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
