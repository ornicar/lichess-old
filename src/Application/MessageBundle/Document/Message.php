<?php

namespace Application\MessageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Ornicar\MessageBundle\Document\Message as BaseMessage;

/**
 * @MongoDB\Document(
 *   repositoryClass="Ornicar\MessageBundle\Document\MessageRepository",
 *   collection="message"
 * )
 */
class Message extends BaseMessage
{
    /**
     * @MongoDB\ReferenceOne(targetDocument="Application\UserBundle\Document\User")
     */
    protected $from;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Application\UserBundle\Document\User")
     */
    protected $to;
}
