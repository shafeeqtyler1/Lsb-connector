<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\Cache;

/**
 * Simple cache interface compatible with PSR-16
 */
interface CacheInterface
{
    /**
     * Fetches a value from the cache.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Persists data in the cache.
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * Delete an item from the cache by its unique key.
     */
    public function delete(string $key): bool;

    /**
     * Determines whether an item is present in the cache.
     */
    public function has(string $key): bool;
}
