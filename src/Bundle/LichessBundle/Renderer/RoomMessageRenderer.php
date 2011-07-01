<?php

namespace Bundle\LichessBundle\Renderer;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Uses the container to lazy load the translator,
 * as it is quite heavy in memory
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class RoomMessageRenderer
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
            $text = $this->getTranslator()->trans($text);
        }

        $text = nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));

        return sprintf('<li class="%s">%s</li>', $author, $text);
    }

    protected function getTranslator()
    {
        $translator = $this->container->get('translator');

        if (!$translator->getLocale() && ($locale = $this->container->get('request')->query->get('l'))) {
            $translator->setLocale($locale);
        }

        return $translator;
    }
}
