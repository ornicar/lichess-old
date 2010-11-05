<?php

namespace Bundle\LichessBundle\Document;

/**
 * @mongodb:EmbeddedDocument
 */
class Room
{
    /**
     * List of room messages
     *
     * @var array
     * @mongodb:Field(type="collection", name="m")
     */
    protected $messages = array();

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
        $this->messages[] = array($user, $message);
    }

    /**
     * Get the number of messages
     *
     * @return int
     **/
    public function getNbMessages()
    {
        return count($this->messages);
    }
}
