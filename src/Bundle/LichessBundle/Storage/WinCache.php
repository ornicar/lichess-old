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
        // @todo
        $cache = wincache_ucache_info();
        $entries = (array) $cache['ucache_entries'];

        $entries = array_filter($entries, function($entry) use ($regex) {
            return (bool) preg_match($regex, $entry['key_name']);
        });

        $entries = array_map(function($entry) {
            $entry['key'] = $entry['key_name'];
            $entry['mtime'] = time() - $entry['age_seconds'];
            return $entry;
        }, $entries);

        return $entries;
    }
}