<?php

namespace Bundle\LichessBundle\Persistence;

class FilePersistence implements PersistenceInterface
{
    protected $dir;

    public function __construct($dir)
    {
        if(!is_string($dir) || !is_dir($dir))
        {
            throw new \Exception($dir.' is not a directory');
        }

        if(!is_writable($dir))
        {
            throw new \Exception($dir.' is not writable');
        }

        $this->dir = $dir;
    }

    public function save($game)
    {
        $data = serialize($game);
        $data = $this->encode($data);
        $file = $this->getGameFile($game);
        if(!file_put_contents($file, $data))
        {
            throw new Exception('Can not save game '.$game->getHash().' to '.$this->getGameFile($game));
        }
        
        $game->setUpdatedAt(time());
    }

    /**
     * @param string $hash
     * @return Game
     */
    public function find($hash)
    {
        $file = $this->dir.'/'.$hash;

        if(!\file_exists($file)) {
            throw new \Exception('Game file '.$file.' does not exist');
        }

        $data = file_get_contents($file);
        $data = $this->decode($data);
        $game = \unserialize($data);

        if(!$game) {
            return null;
        }

        $game->setUpdatedAt(filemtime($file));

        return $game;
    }

    protected function encode($data)
    {
        return gzcompress($data, 1);
    }

    protected function decode($data)
    {
        return gzuncompress($data);
    }

    public function getUpdatedAt($hash)
    {
        $file = $this->dir.'/'.$hash;

        if(!\file_exists($file))
        {
            throw new \Exception('Game file '.$file.' does not exist');
        }

        return filemtime($file);
    }

    public function getGameFile($game)
    {
        return $this->dir.'/'.$game->getHash();
    }
}
