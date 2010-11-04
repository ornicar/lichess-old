<?php

namespace Bundle\LichessBundle\Document;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @mongodb:EmbeddedDocument
 */
class Room
{
    /**
     * List of room messages
     *
     * @var Collection
     * @mongodb:Field(type="collection")
     */
    protected $messages = null;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    /**
     * Get messages
     * @return Collection
     */
    public function getMessages()
    {
      return $this->messages;
    }

    /**
     * Add a message to the room
     *
     * @param string $user The user who says the message
     * @param string $message The message
     * @return null
     **/
    public function addMessage($user, $message)
    {
        $user = (string) $user;
        $message = (string) $message;
        $this->messages->add(array($user, $message));
    }

}
