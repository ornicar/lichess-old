<?php

namespace Application\Ornicar\MessageBundle\Document;

use Bundle\Ornicar\MessageBundle\Document\Message as BaseMessage;

/**
 * @mongodb:Document(
 *   repositoryClass="Bundle\Ornicar\MessageBundle\Document\MessageRepository",
 *   collection="message"
 * )
 */
class Message extends BaseMessage
{
    /**
     * @mongodb:ReferenceOne(targetDocument="Application\FOS\UserBundle\Document\User")
     */
    protected $from;

    /**
     * @mongodb:ReferenceOne(targetDocument="Application\FOS\UserBundle\Document\User")
     */
    protected $to;
}
