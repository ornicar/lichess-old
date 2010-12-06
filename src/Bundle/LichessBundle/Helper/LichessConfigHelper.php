<?php

namespace Bundle\LichessBundle\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Translation\Translator;

class LichessConfigHelper extends Helper
{
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function choices($choices)
    {
        $translated = array();
        foreach($choices as $choice) {
            $translated[] = $this->translator->trans($choice);
        }

        return implode(', ', $translated);
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
