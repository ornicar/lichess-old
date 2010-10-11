<?php

namespace Bundle\LichessBundle\Persistence;
use Bundle\LichessBundle\Entities\Game;
use Bundle\LichessBundle\Entities\Player;

class MongoDBPersistence
{
    protected $mongo;
    protected $collection;
    protected $games = array();

    public function __construct()
    {
        $this->mongo = new \Mongo();
        $this->collection = $this->mongo->selectCollection('lichess', 'game');
    }

    public function save(Game $game)
    {
        foreach($game->getPlayers() as $player) {
            if(!$player->getIsAi()) {
                $player->getStack()->rotate();
                $this->storePlayerCache($player);
            }
        }

        $gameData = array(
            'serialized' => $this->encode(serialize($game)),
            'hash' => $game->getHash()
        );

        $criteria = array('hash' => $gameData['hash']);
        $options = array('upsert' => true);
        $this->collection->update($criteria, $gameData, $options);
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
        $this->collection->remove(array('hash' => $game->getHash()));
    }

    public function clearPlayerCache(Player $player)
    {
        apc_delete($player->getGame()->getHash().'.'.$player->getColor().'.data');
    }

    /**
     * @param string $hash
     * @return Game
     */
    public function find($hash)
    {
        if(isset($this->games[$hash])) return $this->games[$hash];

        $gameData = $this->collection->findOne(array('hash' => $hash));

        if(!$gameData) return null;

        $game = unserialize($this->decode($gameData['serialized']));

        if(!$game) return null;

        return $this->games[$hash] = $game;
    }

    protected function encode($data)
    {
        return new \MongoBinData(gzcompress($data, 1));
    }

    protected function decode($data)
    {
        return gzuncompress($data->bin);
    }
}
