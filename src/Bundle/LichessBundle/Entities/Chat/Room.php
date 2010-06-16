<?php

namespace Bundle\LichessBundle\Entities\Chat;

class Room
{
    /**
     * List of room messages
     *
     * @var array
     */
    protected $messages = array();
    
    /**
     * Get messages
     * @return array
     */
    public function getMessages()
    {
      return $this->messages;
    }
    
    /**
     * Set messages
     * @param  array
     * @return null
     */
    public function setMessages($messages)
    {
      $this->messages = $messages;
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
        $this->messages[] = array($user, $message);
    }

}
