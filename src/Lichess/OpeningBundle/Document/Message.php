<?php

namespace Lichess\OpeningBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Application\UserBundle\Document\User;

/**
 * Represents a home chat message
 *
 * @MongoDB\Document(
 *   collection="lobby_message",
 *   repositoryClass="Lichess\OpeningBundle\Document\MessageRepository"
 * )
 */
class Message
{
    /**
     * @MongoDB\Id(strategy="increment")
     */
    protected $id;

    /**
     * The user name
     *
     * @MongoDB\String
     * @MongoDB\Index
     * @var string
     */
    protected $username;

    /**
     * The client IP (for flood control)
     *
     * @MongoDB\String
     * @var string
     */
    protected $ip;

    /**
     * @MongoDB\String
     */
    protected $message;

    protected $maxLength = 200;

    public function __construct($username, $message, $ip)
    {
        $this->username = $username;
        $this->message = $this->processMessage($message);
        $this->ip = $ip;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Get username
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getIp()
    {
        return $this->ip;
    }

    protected function processMessage($message)
    {
        $message = trim($message);
        $message = wordwrap($message, $this->maxLength);
        $message = preg_replace('#lichess\.org/([\w-]{8})[\w-]{4}#si', 'lichess.org/$1', $message);

        return $message;
    }

    public function isValid()
    {
        return $this->message != "";
    }

    public function isLike(Message $m)
    {
        return $m->getMessage() == $this->getMessage() && $this->isSameClient($m);
    }

    public function isSameClient(Message $m)
    {
        return $m->getUsername() == $this->getUsername() && $m->getIp() == $this->getIp();
    }

    public function __toString()
    {
        return $this->getUsername().': '.$this->getMessage();
    }
}
