<?php

namespace Lichess\OpeningBundle\Sync;

use Lichess\OpeningBundle\Sync\Memory;
use Lichess\OpeningBundle\Document\HookRepository;

class HookCleaner
{
    /**
     * Sync memory
     *
     * @var Memory
     */
    protected $memory;

    /**
     * Hook repository
     *
     * @var HookRepository
     */
    protected $hookRepository;

    public function __construct(Memory $memory, HookRepository $hookRepository)
    {
        $this->memory = $memory;
        $this->hookRepository = $hookRepository;
    }

    public function removeDeadHooks()
    {
        if (0 == time()%10) {
            $this->hookRepository->removeOldHooks();
        }
        $hooks = $this->hookRepository->findAllOpen();
        foreach ($hooks as $hook) {
            if (!$this->memory->isAlive($hook)) {
                $this->hookRepository->getDocumentManager()->remove($hook);
            }
        }
    }
}
