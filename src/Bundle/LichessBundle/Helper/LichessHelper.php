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

    public function getNbConnectedPlayers()
    {
        return $this->synchronizer->getNbConnectedPlayers();
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
