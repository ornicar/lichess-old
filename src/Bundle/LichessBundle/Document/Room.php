<?php

namespace Bundle\LichessBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(
 *   collection="game_room",
 *   repositoryClass="Bundle\LichessBundle\Document\RoomRepository"
 * )
 */
class Room
{
    /**
     * Unique ID of the game
     *
     * @var string
     * @MongoDB\Id(strategy="none")
     */
    protected $id;

    /**
     * List of room messages
     *
     * @var array
     * @MongoDB\Field(type="collection")
     */
    protected $messages = array();

    public function __construct($id, array $messages = array())
    {
        $this->id = $id;
        $this->messages = $messages;
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
