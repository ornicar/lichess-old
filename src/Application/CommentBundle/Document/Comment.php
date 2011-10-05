<?php

namespace Application\CommentBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use FOS\CommentBundle\Document\Comment as BaseComment;
use FOS\CommentBundle\Model\SignedCommentInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @MongoDB\Document(
 *   collection="fos_comment_comment"
 * )
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"thread.$id"="asc"}),
 *   @MongoDB\Index(keys={"ancestors"="asc"})
 * })
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
     * The thread
     *
     * @MongoDB\ReferenceOne(targetDocument="Application\CommentBundle\Document\Thread")
     * @var Thread
     */
    protected $thread;

    /**
     * @return Thread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * @param Thread
     */
    public function setThread($thread)
    {
        $this->thread = $thread;
    }
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
        return preg_replace('/^game\:(.+)$/', '$1', $this->getThread()->getId());
    }
}
