<?php

namespace Lichess\OpeningBundle\Sync;

use Lichess\OpeningBundle\Document\HookRepository;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Lichess\OpeningBundle\Config\GameConfigView;
use Lichess\OpeningBundle\Document\Hook;

class HooksRenderer
{
    protected $repository;
    protected $translator;
    protected $router;
    protected $request;

    public function __construct(HookRepository $repository, TranslatorInterface $translator, UrlGeneratorInterface $router, Request $request)
    {
        $this->repository = $repository;
        $this->translator = $translator;
        $this->router = $router;
        $this->request = $request;
    }

    public function render($auth, $myHookId)
    {
        if ($auth == 1) {
            $hooks = $this->repository->findAllOpen()->toArray();
        } else {
            $hooks = $this->repository->findAllOpenCasual()->toArray();
        }
        $translator = $this->translator;
        $translator->setLocale($this->request->query->get('l'));
        $router = $this->router;

        $data = array();
        if (empty($hooks)) {
            $data['message'] = $translator->trans('No game available right now, create one!');
        } else {
            $data['hooks'] = array_map(function(Hook $hook) use ($translator, $router, $myHookId) {
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
