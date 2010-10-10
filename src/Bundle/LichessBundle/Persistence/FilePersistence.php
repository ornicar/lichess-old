<?php

namespace Bundle\LichessBundle\Persistence;
use Bundle\LichessBundle\Entities\Game;
use Bundle\LichessBundle\Entities\Player;

class FilePersistence
{
    protected $dir;
    protected $games = array();

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

    public function save(Game $game)
    {
        foreach($game->getPlayers() as $player) {
            if(!$player->getIsAi()) {
                $player->getStack()->rotate();
                $this->storePlayerCache($player);
            }
        }
        $data = serialize($game);
        $data = $this->encode($data);
        $file = $this->getGameFile($game);
        if(!file_put_contents($file, $data))
        {
            throw new \Exception('Can not save game '.$game->getHash().' to '.$this->getGameFile($game));
        }
    }

    public function storePlayerCache(Player $player)
    {
        apc_store($player->getGame()->getHash().'.'.$player->getColor().'.data', $player->getStack()->getVersion(), 3600);
    }

    public function remove(Game $game)
    {
        foreach($game->getPlayers() as $player) {
            if(!$player->getIsAi()) {
                $this->clearPlayerCache($player);
            }
        }
        $file = $this->getGameFile($game);
        if(!unlink($file))
        {
            throw new \Exception('Can not remove game '.$game->getHash().' at '.$this->getGameFile($game));
        }
    }

    public function clearPlayerCache(Player $player)
    {
        apc_store($player->getGame()->getHash().'.'.$player->getColor().'.data', null, 1);
    }

    /**
     * @param string $hash
     * @return Game
     */
    public function find($hash)
    {
        if(isset($this->games[$hash])) return $this->games[$hash];

        $file = $this->dir.'/'.$hash;

        if(!\file_exists($file)) {
            return null;
        }

        $data = file_get_contents($file);
        $data = $this->decode($data);
        $game = \unserialize($data);

        if(!$game) return null;

        return $this->games[$hash] = $game;
    }

    protected function encode($data)
    {
        return gzcompress($data, 1);
    }

    protected function decode($data)
    {
        return gzuncompress($data);
    }

    public function getGameFile(Game $game)
    {
        return $this->dir.'/'.$game->getHash();
    }
}
