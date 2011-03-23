<?php

namespace Bundle\LichessBundle\Session\Storage;

use Symfony\Component\HttpFoundation\SessionStorage\SessionStorageInterface;

class FileSessionStorage implements SessionStorageInterface
{
    protected $dir;
    protected $data;
    protected $name;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    protected function getFile()
    {
        return $this->dir.'/test-session';
    }

    public function start($name = 'test-session-name')
    {
        $this->data = array();
        $this->name = $name;
        $this->load();
    }

    public function load()
    {
        if($serialized = @file_get_contents($this->getFile())) {
            $this->data = unserialize($serialized);
        }
    }

    public function save()
    {
        $serialized = serialize($this->data);
        file_put_contents($this->getFile(), $serialized);
    }

    public function deleteFile()
    {
        @unlink($this->getFile());
    }

    public function read($key, $default = null)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    public function regenerate($destroy = false)
    {
        if ($destroy) {
            $this->data = array();
        }
        return true;
    }

    public function remove($key)
    {
        unset($this->data[$key]);
    }

    public function getId()
    {
    }

    public function write($key, $data)
    {
        $this->data[$key] = $data;
        $this->save();
    }
}
