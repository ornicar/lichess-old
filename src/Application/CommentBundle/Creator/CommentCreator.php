<?php

namespace Application\CommentBundle\Creator;

use Symfony\Component\HttpFoundation\Request;
use FOS\CommentBundle\Creator\DefaultCommentCreator;
use FOS\CommentBundle\Model\CommentManagerInterface;
use FOS\CommentBundle\Model\CommentInterface;
use FOS\CommentBundle\Blamer\CommentBlamerInterface;
use FOS\CommentBundle\Akismet;
use Bundle\LichessBundle\Document\TimelineEntryRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Templating\EngineInterface as Templating;

/**
 * @see CommentCreatorInterface
 */
class CommentCreator extends DefaultCommentCreator
{
    protected $request;
    protected $commentManager;
    protected $commentBlamer;
    protected $timeline;
    protected $objectManager;
    protected $templating;
    protected $akismet;

    public function __construct(Request $request, CommentManagerInterface $commentManager, CommentBlamerInterface $commentBlamer, TimelineEntryRepository $timeline, DocumentManager $objectManager, Templating $templating, Akismet $akismet = null)
    {
        $this->timeline      = $timeline;
        $this->objectManager = $objectManager;
        $this->templating    = $templating;

        parent::__construct($request, $commentManager, $commentBlamer, $akismet);
    }

    public function create(CommentInterface $comment)
    {
        $success = parent::create($comment);
        if ($success) {
            if ($gameId = $comment->getGameId()) {
                $entry = $this->templating->render('FOSCommentBundle:Comment:timelineEntry.html.twig', array(
                    'comment' => $comment,
                    'game_id' => $gameId
                ));
                $this->timeline->add('comment_game', $entry, $comment->getAuthor());
                $this->objectManager->flush();
            }
        }

        return $success;
    }
}
