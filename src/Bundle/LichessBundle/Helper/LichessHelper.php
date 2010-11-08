<?php

namespace Bundle\LichessBundle\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Bundle\LichessBundle\Chess\Synchronizer;

class LichessHelper extends Helper
{
    protected $synchronizer;

    public function __construct(Synchronizer $synchronizer)
    {
        $this->synchronizer = $synchronizer;
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
