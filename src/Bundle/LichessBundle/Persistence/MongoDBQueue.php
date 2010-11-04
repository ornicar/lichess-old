<?php

namespace Bundle\LichessBundle\Persistence;
use Bundle\LichessBundle\Entities\Game;
use Bundle\LichessBundle\Chess\Generator;

class MongoDBQueue
{
    protected $server = 'mongodb://localhost:27017';
    protected $mongo;
    protected $collection;

    const QUEUED = 1;
    const FOUND = 2;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
        $this->mongo = new \Mongo($this->server, array('persist' => 'lichess_connection'));
        $this->collection = $this->mongo->selectCollection('lichess', 'queue');
    }

    public function add(QueueEntry $entry, $color)
    {
        if($existing = $this->searchMatching($entry)) {
            $this->remove($existing);

            return array(
                'status' => static::FOUND,
                'game_hash' => $existing->gameHash,
                'time' => $entry->getCommonTime($existing),
                'variant' => $entry->getCommonVariant($existing)
            );
        }

        $game = $this->generator->createGameForPlayer($color)->getGame();

        $this->collection->insert(array(
            'times' => $entry->times,
            'variants' => $entry->variants,
            'gameHash' => $game->getHash(),
            'userId' => $entry->userId,
            'date' => time()
        ));

        return array('status' => static::QUEUED, 'game' => $game);
    }

    public function clear()
    {
        $this->collection->remove(array());
    }

    public function remove(QueueEntry $entry)
    {
        $this->collection->remove(array('_id' => new \MongoId($entry->id)));
    }

    public function count()
    {
        return $this->collection->count();
    }

    protected function searchMatching(QueueEntry $entry)
    {
        $cursor = $this->collection->find()->sort(array('date' => 1));
        foreach($cursor as $data) {
            $existing = $this->hydrate($data);
            if($existing->match($entry)) {
                return $existing;
            }
        }
    }

    protected function hydrate(array $data)
    {
        $entry = new QueueEntry($data['times'], $data['variants'], $data['userId']);
        $entry->id = $data['_id']->__toString();
        $entry->gameHash = $data['gameHash'];

        return $entry;
    }
}
