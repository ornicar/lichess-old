<?php

namespace Bundle\LichessBundle\Timeline;

use Bundle\LichessBundle\Document\TimelineEntryRepository;
use Symfony\Component\Templating\EngineInterface as Templating;

abstract class AbstractPusher
{
    protected $timeline;
    protected $templating;

    public function __construct(TimelineEntryRepository $timeline, Templating $templating)
    {
        $this->timeline      = $timeline;
        $this->templating    = $templating;
    }
}
