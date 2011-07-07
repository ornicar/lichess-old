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
        $manager = $this->hookRepository->getDocumentManager();
        $hooks = $this->hookRepository->findAllOpen();
        foreach ($hooks as $hook) {
            if (!$this->memory->isAlive($hook)) {
                $manager->remove($hook);
            }
        }
        $manager->flush();
        $this->hookRepository->removeOldHooks();
    }
}
