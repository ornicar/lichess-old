<?php

namespace Application\ForumBundle;

class NewPostsCache
{
    public function __construct($key, $lifetime)
    {
        $this->key = $key;
        $this->lifetime = $lifetime;
    }

    public function getCache()
    {
        return apc_fetch($this->key);
    }

    public function setCache($text)
    {
        apc_store($this->key, $text, $this->lifetime);
    }

    public function invalidate()
    {
        apc_delete($this->key);
    }
}
