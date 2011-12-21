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
     * @MongoDB\Index(order="desc")
     */
    protected $id;

    /**
     * The user name
     *
     * @MongoDB\String
     * @var string
     */
    protected $username;

    /**
     * @MongoDB\Boolean
     */
    protected $registered;

    /**
     * @MongoDB\String
     */
    protected $message;

    protected $maxLength = 200;

    public function __construct($user, $message)
    {
        if ($user instanceof User) {
            $this->registered = true;
            $this->username = $user->getUsername();
        } else {
            $this->registered = false;
            $this->username = $user;
        }
        $this->message = $this->processMessage($message);
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

    public function isRegistered()
    {
        return (bool) $this->registered;
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

    public function __toString()
    {
        return $this->getUsername().': '.$this->getMessage();
    }
}
