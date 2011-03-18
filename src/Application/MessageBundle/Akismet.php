<?php

namespace Application\MessageBundle;

use Zend\Service\Akismet\Akismet as ZendAkismet;
use Symfony\Component\HttpFoundation\Request;
use Application\MessageBundle\Document\Message;
use Zend\Service\Akismet\Exception as AkismetException;

class Akismet
{
    protected $request;
    protected $akismet;
    protected $enabled;

    public function __construct(Request $request, ZendAkismet $akismet, $enabled)
    {
        $this->request = $request;
        $this->akismet = $akismet;
        $this->enabled = (bool) $enabled;
    }

    public function isMessageSpam(Message $message)
    {
        if (!$this->enabled) {
            return $message->getSubject() == 'viagra-test-123';
        }
        $data = array_merge($this->getRequestData(), $this->getMessageData($message));

        return $this->isSpam($data);
    }

    protected function isSpam(array $data)
    {
        try {
            return $this->akismet->isSpam($data);
        } catch (AkismetException $e) {
            return true;
        }
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

    protected function getRequestData()
    {
        $server = $this->request->server;

        return array(
            'user_ip'    => $this->request->getClientIp(),
            'user_agent' => $server->get('HTTP_USER_AGENT'),
            'referrer'   => $server->get('HTTP_REFERER'),
        );
    }
}
