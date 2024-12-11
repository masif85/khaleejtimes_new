<?php declare(strict_types=1);

namespace Everyware\Concepts\Contracts;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Interface SimpleCacheHandler
 * @package Everyware\Concepts\Contracts
 */
interface SimpleCacheHandler extends CacheInterface
{
    /**
     * Retrieve an item from the cache or
     * Will persists data in the cache, uniquely referenced by a key with an expiration TTL time,
     * if the requested item doesn't exist.
     *
     * @param string   $key                    The key of the item to store.
     * @param int      $ttl                    The TTL value of this item. If no value is sent and
     *                                         the driver supports TTL then the library may set a default value
     *                                         for it or let the driver take care of that.
     * @param callable $callback               The callback to provide the item to store, must be serializable.
     *                                         Will not store if callable returns null.
     *
     * @return mixed The cached or newly set value. Will return null if closure or storing failed
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function remember($key, $ttl, callable $callback = null);

    /**
     * Fetches a value from the cache and then delete the item.
     *
     * @param string $key     The unique key of this item in the cache.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function pull($key);
}
