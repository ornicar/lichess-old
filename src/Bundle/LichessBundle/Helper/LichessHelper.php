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
        $loadAverage = sys_getloadavg();

        return round(25*$loadAverage[1]).'%';
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

    public function roomMessage($author, $message)
    {
        if('system' === $author) {
            $message = $this->translator->trans($message);
        }

        return sprintf('<li class="%s">%s</li>', $author, $this->userText($message));
    }

    public function roomMessages(array $messages)
    {
        $html = '';
        foreach($messages as $message) {
            $html .= $this->roomMessages($message[0], $message[1]);
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
