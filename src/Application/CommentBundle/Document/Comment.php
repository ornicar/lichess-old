<?php

namespace Application\CommentBundle\Document;

use FOS\CommentBundle\Document\Comment as BaseComment;
use FOS\CommentBundle\Model\SignedCommentInterface;
use Application\UserBundle\Document\User;

/**
 * @mongodb:Document(
 *   collection="fos_comment_comment"
 * )
 */
class Comment extends BaseComment implements SignedCommentInterface
{
    /**
     * The author name
     *
     * @mongodb:String
     * @var string
     */
    protected $authorName = 'Anonymous';

    /**
     * The author user if any
     *
     * @mongodb:ReferenceOne(targetDocument="Application\UserBundle\Document\User")
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
     * @param  FOSUser
     * @return null
     */
    public function setAuthor($author)
    {
        $this->author = $author;
        $this->authorName = $author->getUsername();
    }

    /**
     * Convenience method for the default security blamer
     *
     * @return null
     **/
    public function setUser(User $user)
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
