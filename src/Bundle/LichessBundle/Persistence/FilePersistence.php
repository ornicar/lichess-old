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
    if(!file_put_contents($this->getGameFile($game), serialize($game)))
    {
      throw new Exception('Can not save game '.$game->getHash().' to '.$this->getGameFile($game));
    }
  }

  /**
   * @param string $hash
   * @return Game
   */
  public function find($hash)
  {
    $file = $this->dir.'/'.$hash;

    if(!\file_exists($file))
    {
      throw new \Exception('Game file '.$file.' does not exist');
    }
    
    $game = \unserialize(file_get_contents($file));
    
    if(!$game)
    {
      throw new \Exception('Can not load game from '.$file.': got a '.gettype($game));
    }

    return $game;
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
