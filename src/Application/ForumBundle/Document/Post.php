<?php

namespace Application\ForumBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Herzult\Bundle\ForumBundle\Document\Post as BasePost;
use Application\UserBundle\Document\User;

/**
 * @MongoDB\Document(
 *   repositoryClass="Herzult\Bundle\ForumBundle\Document\PostRepository",
 *   collection="forum_post"
 * )
 */
class Post extends BasePost
{
    // id of the checkmate game (captcha)
    public $checkmateId;

    public $checkmateMove;

    public $checkmateSolutions;

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

    /**
     * @Assert\True(message = "You failed to checkmate!")
     */
    public function isCaptchaSolved() 
    {
        return in_array($this->cleanMove($this->checkmateMove), array_map(array($this, 'cleanMove'), $this->checkmateSolutions));
    }

    private function cleanMove($move)
    {
        return str_replace(' ', '', strtolower($move));
    }

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
