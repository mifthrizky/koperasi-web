<?php

namespace App\Session;

use MongoDB\Collection;
use SessionHandlerInterface;
use MongoDB\BSON\UTCDateTime;

class MongoSessionHandler implements SessionHandlerInterface
{
    protected $collection;
    protected $lifetime;

    public function __construct(Collection $collection, $lifetime = 120)
    {
        $this->collection = $collection;
        $this->lifetime = $lifetime * 60; // minutes → seconds
    }

    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($sessionId): string
    {
        $session = $this->collection->findOne([
            '_id' => $sessionId,
            'expires_at' => ['$gt' => new UTCDateTime()]
        ]);

        return $session['payload'] ?? '';
    }

    public function write($sessionId, $data): bool
    {
        $this->collection->updateOne(
            ['_id' => $sessionId],
            [
                '$set' => [
                    'payload'    => $data,
                    'expires_at' => new UTCDateTime((time() + $this->lifetime) * 1000),
                ]
            ],
            ['upsert' => true]
        );

        return true;
    }

    public function destroy($sessionId): bool
    {
        $this->collection->deleteOne(['_id' => $sessionId]);
        return true;
    }

    public function gc($maxlifetime): int|false
    {
        $result = $this->collection->deleteMany([
            'expires_at' => ['$lt' => new UTCDateTime()]
        ]);

        return $result->getDeletedCount();
    }
}
