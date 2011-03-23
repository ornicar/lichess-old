<?php

namespace Bundle\LichessBundle\Renderer;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class RoomMessageRenderer
{
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Renders A message in the chat
     *
     * @return string
     **/
    public function renderRoomMessage(array $message)
    {
        list($author, $text) = $message;

        if('system' === $author) {
            $text = $this->translator->trans($text);
        }

        $text = nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));

        return sprintf('<li class="%s">%s</li>', $author, $text);
    }
}
