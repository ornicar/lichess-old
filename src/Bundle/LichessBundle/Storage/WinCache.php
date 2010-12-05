<?php

namespace Bundle\LichessBundle\Storage;

class WinCache implements StorageInterface
{
    public function store($key, $data, $ttl = null)
    {
        return wincache_ucache_set($key, $data, $ttl);
    }

    public function get($key)
    {
        return wincache_ucache_get($key);
    }

    public function delete($key)
    {
        return wincache_ucache_delete($key);
    }

    public function getIterator($regex)
    {
        // @todo need ApcIterator implementation for wincache
        return array();
    }
}