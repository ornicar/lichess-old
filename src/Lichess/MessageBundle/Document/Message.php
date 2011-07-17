<?php

namespace Lichess\MessageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Ornicar\MessageBundle\Document\Message as BaseMessage;
use FOS\UserBundle\Model\UserInterface;

/**
 * @MongoDB\Document(
 *   collection="message_message"
 * )
 */
class Message extends BaseMessage
{
    /**
     * @var \MongoId
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @var Thread
     * @MongoDB\ReferenceOne(targetDocument="Lichess\MessageBundle\Document\Thread")
     */
    protected $thread;

    /**
     * @var UserInterface
     * @MongoDB\ReferenceOne(targetDocument="Application\UserBundle\Document\User")
     */
    protected $sender;

    /**
     * Used in fixtures
     *
     * @param \DateTime $date
     */
    public function setCreatedAt(\DateTime $date)
    {
        return $this->createdAt = $date;
    }
}
