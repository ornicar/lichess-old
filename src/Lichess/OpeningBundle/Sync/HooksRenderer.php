<?php

namespace Lichess\OpeningBundle\Sync;

use Lichess\OpeningBundle\Document\HookRepository;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Lichess\OpeningBundle\Config\GameConfigView;
use Lichess\OpeningBundle\Document\Hook;

class HooksRenderer
{
    protected $repository;
    protected $translator;
    protected $request;

    public function __construct(HookRepository $repository, TranslatorInterface $translator, Request $request)
    {
        $this->repository = $repository;
        $this->translator = $translator;
        $this->request = $request;
    }

    public function render($auth, $myHookId)
    {
        if ($auth) {
            $hooks = $this->repository->findAllOpen()->toArray();
        } else {
            $hooks = $this->repository->findAllOpenCasual()->toArray();
        }
        $translator = $this->translator;
        $translator->setLocale($this->request->query->get('l'));

        $data = array();
        if (empty($hooks)) {
            $data['message'] = $translator->trans('No game available right now, create one!');
        } else {
            $data['hooks'] = array_map(function(Hook $hook) use ($translator, $myHookId) {
                $config = new GameConfigView($hook->toArray());
                $array = array(
                    'username' => $hook->getUsername(),
                    'elo' => $hook->getElo(),
                    'variant' => $translator->trans($config->getVariant()),
                    'mode' => $translator->trans($config->getMode()),
                    'color' => lcfirst($config->getColor()),
                    'clock' => $config->getClock() ? sprintf('%d + %d', $config->getTime(), $config->getIncrement()) : $translator->trans($config->getTime())
                );
                if ($myHookId === $hook->getOwnerId()) {
                    $array['action'] = 'cancel';
                    $array['id'] = $hook->getOwnerId();
                } else {
                    $array['action'] = 'join';
                    $array['id'] = $hook->getId();
                }
                return $array;
            }, $hooks);
        }

        return $data;
    }
}
