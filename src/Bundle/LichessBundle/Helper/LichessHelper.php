<?php

namespace Bundle\LichessBundle\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Translation\Translator;
use Bundle\LichessBundle\Chess\Synchronizer;

class LichessHelper extends Helper
{
    protected $synchronizer;
    protected $translator;

    public function __construct(Synchronizer $synchronizer, Translator $translator)
    {
        $this->synchronizer = $synchronizer;
        $this->translator = $translator;
    }

    public function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    public function getNbConnectedPlayers()
    {
        return $this->synchronizer->getNbConnectedPlayers();
    }

    public function getLoadAverage()
    {
        if (function_exists('sys_getloadavg')) {
            $loadAverage = sys_getloadavg();

            return round(25*$loadAverage[1]).'%';
        } else {
            // @todo need an implementation for windows
            return 0;
        }
    }

    public function autoLink($text)
    {
        return TextHelper::autoLink($text);
    }

    public function userText($text)
    {
        return nl2br($this->autoLink($this->escape($text)));
    }

    public function shorten($text, $length = 140)
    {
        return mb_substr(str_replace("\n", ' ', $this->escape($text)), 0, 140);
    }

    public function getCurrentUrl()
    {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'http://test/';
    }

    public function roomMessage(array $message)
    {
        if('system' === $message[0]) {
            $message[1] = $this->translator->trans($message[1]);
        }

        return sprintf('<li class="%s">%s</li>', $message[0], $this->userText($message[1]));
    }

    public function roomMessages(array $messages)
    {
        $html = '';
        foreach($messages as $message) {
            $html .= $this->roomMessage($message);
        }

        return $html;
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'lichess';
    }
}
