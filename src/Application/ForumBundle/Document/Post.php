<?php

namespace Application\ForumBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Herzult\Bundle\ForumBundle\Document\Post as BasePost;
use Application\UserBundle\Document\User;
use Application\ForumBundle\Spam;

/**
 * @MongoDB\Document(
 *   repositoryClass="Herzult\Bundle\ForumBundle\Document\PostRepository",
 *   collection="forum_post"
 * )
 * @Spam
 */
class Post extends BasePost
{
    /**
     * @MongoDB\ReferenceOne(targetDocument="Application\ForumBundle\Document\Topic")
     */
    protected $topic;

    /**
     * The author name
     *
     * @MongoDB\String
     * @var string
     */
    protected $authorName = '';

    /**
     * The author user if any
     *
     * @MongoDB\ReferenceOne(targetDocument="Application\UserBundle\Document\User")
     * @var User
     */
    protected $author = null;

    /**
     * @Assert\MaxLength(10000)
     */
    protected $message;

    public function isStaff()
    {
        return $this->getTopic()->getCategory()->isStaff();
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    public function hasAuthor()
    {
        return null !== $this->author;
    }

    public function isAnon()
    {
        return !$this->hasAuthor();
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
        $message = preg_replace('#lichess\.org/([\w-]{8})[\w-]{4}#si', 'lichess.org/$1', $message);

        return $message;
    }
}
