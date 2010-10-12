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

    public function ensureIndexes()
    {
        $this->collection->deleteIndexes();
        $this->collection->ensureIndex(array('hash' => 1), array('unique' => true, 'safe' => true, 'name' => 'hash_index'));
    }

    public function getNbGames()
    {
        return $this->collection->count();
    }

    public function save(Game $game, $import = false)
    {
        if(!$import) {
            foreach($game->getPlayers() as $player) {
                if(!$player->getIsAi()) {
                    $player->getStack()->rotate();
                    $this->storePlayerCache($player);
                }
            }
        }
        $hash = $game->getHash();

        $data = array(
            'bin' => $this->encode(serialize($game)),
            'hash' => $hash,
            'status' => $game->getStatus(),
            'turns' => $game->getTurns(),
            'upd' => time()
        );

        $criteria = array('hash' => $hash);
        $options = array('upsert' => true);
        $this->collection->update($criteria, $data, $options);
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

        $data = $this->collection->findOne(array('hash' => $hash));
        if(!$data) return null;

        $game = unserialize($this->decode($data['bin']));
        if(!$game) return null;

        return $this->games[$hash] = $game;
    }

    protected function encode($data)
    {
        return new \MongoBinData(gzcompress($data, 9));
    }

    protected function decode($data)
    {
        return gzuncompress($data->bin);
    }
}
