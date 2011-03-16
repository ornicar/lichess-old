<?php

namespace Application\CommentBundle\Creator;

use Symfony\Component\HttpFoundation\Request;
use FOS\CommentBundle\Creator\DefaultCommentCreator;
use FOS\CommentBundle\Model\CommentManagerInterface;
use FOS\CommentBundle\Model\CommentInterface;
use FOS\CommentBundle\Blamer\CommentBlamerInterface;
use FOS\CommentBundle\Akismet;
use Application\CommentBundle\Timeline\Pusher;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * @see CommentCreatorInterface
 */
class CommentCreator extends DefaultCommentCreator
{
    protected $request;
    protected $commentManager;
    protected $commentBlamer;
    protected $timelinePusher;
    protected $objectManager;
    protected $akismet;

    public function __construct(Request $request, CommentManagerInterface $commentManager, CommentBlamerInterface $commentBlamer, Pusher $timelinePusher, DocumentManager $objectManager, Akismet $akismet = null)
    {
        $this->timelinePusher = $timelinePusher;
        $this->objectManager  = $objectManager;

        parent::__construct($request, $commentManager, $commentBlamer, $akismet);
    }

    public function create(CommentInterface $comment)
    {
        $success = parent::create($comment);
        if ($success) {
            $this->timelinePusher->pushComment($comment);
            $this->objectManager->flush();
        }

        return $success;
    }
}
