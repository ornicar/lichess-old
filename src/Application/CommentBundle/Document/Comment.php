<?php

namespace Application\CommentBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use FOS\CommentBundle\Document\Comment as BaseComment;
use FOS\CommentBundle\Model\SignedCommentInterface;
use FOS\UserBundle\Model\UserInterface;

/**
 * @MongoDB\Document(
 *   collection="fos_comment_comment"
 * )
 */
class Comment extends BaseComment implements SignedCommentInterface
{
    /**
     * @var string
     * @MongoDB\Id
     */
    protected $id;

    /**
     * The author name
     *
     * @MongoDB\String
     * @var string
     */
    protected $authorName = 'Anonymous';

    /**
     * The author user if any
     *
     * @MongoDB\ReferenceOne(targetDocument="Application\UserBundle\Document\User")
     * @var User
     */
    protected $author;

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param  UserInterface
     * @return null
     */
    public function setAuthor(UserInterface $author)
    {
        $this->author = $author;
        $this->authorName = $author->getUsername();
    }

    /**
     * Convenience method for the default security blamer
     *
     * @return null
     **/
    public function setUser(UserInterface $user)
    {
        return $this->setAuthor($user);
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
     * @return string
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;
    }

    public function getGameId()
    {
        return preg_replace('/^game\:(.+)$/', '$1', $this->getThread()->getIdentifier());
    }
}
