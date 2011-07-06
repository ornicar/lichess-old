<?php

namespace Application\MessageBundle;

use Ornicar\AkismetBundle\Akismet\AkismetInterface;
use Application\MessageBundle\Document\Message;

class Akismet
{
    protected $akismet;

    public function __construct(AkismetInterface $akismet)
    {
        $this->akismet = $akismet;
    }

    public function isMessageSpam(Message $message)
    {
        return $this->akismet->isSpam($this->getMessageData($message));
    }

    protected function getMessageData(Message $message)
    {
        return array(
            'permalink'       => 'http://lichess.org/inbox',
            'comment_type'    => 'message',
            'comment_author'  => $message->getFrom()->getUsername(),
            'comment_content' => $message->getSubject()."\n".$message->getBody()
        );
    }
}
