<?php

namespace Lichess\MessageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Ornicar\MessageBundle\Document\Thread as BaseThread;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\UserInterface;
use Ornicar\MessageBundle\Model\ParticipantInterface;

/**
 * @MongoDB\Document(
 *   collection="message_thread"
 * )
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"createdBy.$id"="asc"})
 * })
 */
class Thread extends BaseThread
{
    /**
     * @var \MongoId
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @var Collection of MessageInterface
     * @MongoDB\ReferenceMany(targetDocument="Lichess\MessageBundle\Document\Message")
     */
    protected $messages;

    /**
     * @var Collection of UserInterface
     * @MongoDB\ReferenceMany(targetDocument="Application\UserBundle\Document\User")
     */
    protected $participants;

    /**
     * @var UserInterface
     * @MongoDB\ReferenceOne(targetDocument="Application\UserBundle\Document\User")
     */
    protected $createdBy;

    /**
     * Get the participant this user is talking with.
     * Assumes there are only two participants
     *
     * @return ParticipantInterface
     */
    public function getOtherParticipant(ParticipantInterface $participant)
    {
        $participants = $this->getOtherParticipants($participant);

        return reset($participants);
    }

    /**
     * Adds all messages contents to the keywords property
     */
    protected function doKeywords()
    {
        return;
    }
}
