<?php

namespace Bundle\LichessBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Cache
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getNbGames()
    {
        return $this->cache('nb_games', 60, function($container) {
            return $container->get('lichess.repository.game')->getNbGames();
        });
    }

    public function getNbMates()
    {
        return $this->cache('nb_mates', 300, function($container) {
            return $container->get('lichess.repository.game')->getNbMates();
        });
    }

    protected function cache($key, $ttl, \Closure $closure)
    {
        $key = 'lichess2.'.$key;
        $cached = apc_fetch($key);
        if (false === $cached) {
            $cached = $closure($this->container);
            apc_store($key, $cached, $ttl);
        }

        return $cached;
    }
}
