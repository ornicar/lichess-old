<?php

namespace Bundle\LichessBundle\Storage;

class Apc implements StorageInterface
{
    public function store($key, $data, $ttl = null)
    {
        return apc_store($key, $data, $ttl);
    }

    public function get($key)
    {
        return apc_fetch($key);
    }

    public function delete($key)
    {
        return apc_delete($key);
    }

    public function getIterator($regex)
    {
        return new \APCIterator('user', $regex, APC_ITER_MTIME | APC_ITER_KEY, 100, APC_LIST_ACTIVE);
    }
}