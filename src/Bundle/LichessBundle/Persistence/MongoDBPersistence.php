<?php

namespace Bundle\LichessBundle\Persistence;
use Bundle\LichessBundle\Entities\Game;
use Bundle\LichessBundle\Entities\Player;

class MongoDBPersistence
{
    protected $server = 'mongodb://localhost:27017';
    protected $mongo;
    protected $collection;
    protected $games = array();

    public function __construct()
    {
        $this->mongo = new \Mongo($this->server, array('persist' => 'lichess_connection'));
        $this->collection = $this->mongo->selectCollection('lichess', 'game');
    }

    public function ensureIndexes()
    {
        $this->collection->ensureIndex(array('hash' => 1), array('unique' => true, 'safe' => true, 'name' => 'hash_index'));
        $this->collection->ensureIndex(array('upd' => -1), array('unique' => false, 'safe' => true, 'name' => 'upd_index'));
        $this->collection->ensureIndex(array('status' => 1), array('unique' => false, 'safe' => true, 'name' => 'status_index'));
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function getNbGames()
    {
        return $this->collection->count();
    }

    public function findRecentGamesHashes($limit)
    {
        $cursor = $this->getCollection()->find(array('status' => Game::STARTED), array('hash'))->sort(array('upd' => -1))->limit($limit);
        $hashes = array();
        foreach(iterator_to_array($cursor) as $result) {
            $hashes[] = $result['hash'];
        }

        return $hashes;
    }

    public function findGamesByHashes(array $hashes)
    {
        // fetch games from DB
        $cursor = $this->getCollection()->find(array('hash' => array('$in' => $hashes)))->limit(9);
        $data = iterator_to_array($cursor);

        // sort games in the order of hashes
        $hashPos = array_flip($hashes);
        usort($data, function($a, $b) use ($hashPos)
        {
            return $hashPos[$a['hash']] > $hashPos[$b['hash']];
        });

        // hydrate games
        $games = array();
        foreach($data as $gameData) {
            $games[] = $this->decode($gameData['bin']);
        }

        return $games;
    }

    public function save(Game $game)
    {
        foreach($game->getPlayers() as $player) {
            if(!$player->getIsAi()) {
                $player->getStack()->rotate();
                $this->storePlayerCache($player);
            }
        }
        $hash = $game->getHash();

        $data = array(
            'bin' => $this->encode($game),
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

        $game = $this->decode($data['bin']);
        if(!$game) return null;

        return $this->games[$hash] = $game;
    }

    protected function encode($game)
    {
        return new \MongoBinData(gzcompress(serialize($game), 5));
    }

    protected function decode($data)
    {
        return unserialize(gzuncompress($data->bin));
    }

    public function getGameSize(Game $game)
    {
        return strlen($this->encode($game)->bin);
    }
}
