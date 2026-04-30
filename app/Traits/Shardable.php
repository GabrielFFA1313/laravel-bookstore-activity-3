<?php

namespace App\Traits;

trait Shardable
{
    /**
     * Returns the shard connection name based on model ID.
     * Uses modulo-4 routing — distributes records evenly across 4 shards.
     */
    public function getShardConnection(): string
    {
        $shardId = (int)($this->id) % 4;
        return "mysql_shard_{$shardId}";
    }

    /**
     * Get the shard number for this model instance.
     */
    public function getShardId(): int
    {
        return (int)($this->id) % 4;
    }

    /**
     * Determine which shard a given ID belongs to.
     */
    public static function getShardForId(int $id): string
    {
        $shardId = $id % 4;
        return "mysql_shard_{$shardId}";
    }
}