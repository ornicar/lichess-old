<?php

namespace Application\CommentBundle\Creator;

use Symfony\Component\HttpFoundation\Request;
use FOS\CommentBundle\Creator\DefaultCommentCreator;
use FOS\CommentBundle\Model\CommentManagerInterface;
use FOS\CommentBundle\Model\CommentInterface;
use FOS\CommentBundle\Blamer\CommentBlamerInterface;
use FOS\CommentBundle\SpamDetection\SpamDetectionInterface;
use Application\CommentBundle\Timeline\Pusher;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * @see CommentCreatorInterface
 */
class CommentCreator extends DefaultCommentCreator
{
    protected $timelinePusher;
    protected $objectManager;

    public function __construct(Request $request, CommentManagerInterface $commentManager, CommentBlamerInterface $commentBlamer, Pusher $timelinePusher, DocumentManager $objectManager, SpamDetectionInterface $spamDetection)
    {
        $this->timelinePusher = $timelinePusher;
        $this->objectManager  = $objectManager;

        parent::__construct($request, $commentManager, $commentBlamer, $spamDetection);
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
