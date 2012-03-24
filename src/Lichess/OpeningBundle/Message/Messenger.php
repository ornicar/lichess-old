<?php

namespace Lichess\OpeningBundle\Message;

use Lichess\OpeningBundle\Document\MessageRepository;
use Lichess\OpeningBundle\Document\Message;
use Application\UserBundle\Document\User;
use Symfony\Component\HttpFoundation\Request;

class Messenger
{
    public function __construct(MessageRepository $repository, Request $request)
    {
        $this->repository = $repository;
        $this->request = $request;
    }

    public function send(User $user, $text)
    {
        $message = new Message($user->getUsername(), $text, $this->request->getClientIp());

        if ($message->isValid() && !$this->isSpam($message)) {
            $this->repository->add($message);
        }

        return $message;
    }

    public function isSpam(Message $message)
    {
        // temporary IP ban
        if (apc_fetch('chat_ip_ban_' . $message->getIp())) {
          return true;
        }

        $recentMessages = iterator_to_array($this->repository->findRecent(15, true));

        foreach ($recentMessages as $recentMessage) {
            if ($message->isLike($recentMessage)) {
                return true;
            }
        }

        $countSameClient = 0;
        foreach (array_slice($recentMessages, 0, 8) as $recentMessage) {
            if ($message->isSameClient($recentMessage)) {
                $countSameClient++;
            }
        }
        if ($countSameClient == 8) {
            return true;
        }

        $countSameClient = 0;
        foreach ($recentMessages as $recentMessage) {
            if ($message->isSameClient($recentMessage)) {
                $countSameClient++;
            }
        }
        if ($countSameClient >= 13) {
            return true;
        }

        return false;
    }
}
