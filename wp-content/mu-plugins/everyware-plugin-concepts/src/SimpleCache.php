<?php declare(strict_types=1);

namespace Everyware\Concepts;


use Everyware\Concepts\Contracts\SimpleCacheHandler;
use Everyware\Concepts\Exceptions\InvalidCacheKey;
use Everyware\Concepts\Exceptions\InvalidCacheValue;
use Everyware\Concepts\Exceptions\NotSupported;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class SimpleCache
 * @package Everyware\Concepts
 */
class SimpleCache implements SimpleCacheHandler
{
    /**
     * Fetches a value from the cache.
     *
     * @param string $key     The unique key of this item in the cache.
     * @param mixed  $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws InvalidArgumentException
     */
    public function get($key, $default = null)
    {
        if ( ! is_string($key)) {
            throw new InvalidCacheKey('SimpleCache key must be a string');
        }

        if (($value = get_transient($key)) !== false) {
            return $value;
        }

        return $default;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string $key                   The key of the item to store.
     * @param mixed  $value                 The value of the item to store, must be serializable.
     * @param int    $ttl                   Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws InvalidArgumentException
     */
    public function set($key, $value, $ttl = 0): bool
    {
        if ( ! is_string($key)) {
            throw new InvalidCacheKey('SimpleCache key must be a string');
        }

        return set_transient($key, $value, $ttl ?? 0);
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws InvalidArgumentException
     */
    public function delete($key): bool
    {
        if ( ! is_string($key)) {
            throw new InvalidCacheKey('SimpleCache key must be a string');
        }

        return filter_var(delete_transient($key), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @throws NotSupported
     */
    public function clear()
    {
        throw new NotSupported();
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. SimpleCache keys that do not exist or are stale will have
     *                  $default as value.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null)
    {
        $values = [];

        if ( ! is_iterable($keys)) {
            throw new InvalidCacheKey('The cache keys must be either an array or a Traversable.');
        }

        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }

        return $values;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable $values               A list of key => value pairs for a multiple-set operation.
     * @param int      $ttl                  Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = 0): bool
    {
        if ( ! is_iterable($values)) {
            throw new InvalidCacheValue('The cache values must be either an array or a Traversable.');
        }

        $failed = false;

        foreach ($values as $key => $value) {
            if ($this->set($key, $value, $ttl) === false) {
                $failed = true;
            }
        }

        return $failed;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys): bool
    {
        if ( ! is_iterable($keys)) {
            throw new InvalidCacheKey('The cache keys must be either an array or a Traversable.');
        }

        $failed = false;
        foreach ($keys as $key) {
            if ($this->delete($key) === false) {
                $failed = true;
            }
        }

        return $failed;
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has($key): bool
    {
        if ( ! is_string($key)) {
            throw new InvalidCacheKey('SimpleCache key must be a string');
        }

        // Create an unknown value to match with
        $unknownValue = $this->unknownValue();

        return $this->get($key, $unknownValue) !== $unknownValue;
    }

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
    public function remember($key, $ttl, callable $callback = null)
    {
        if ( ! is_string($key)) {
            throw new InvalidCacheKey('SimpleCache key must be a string');
        }

        // Create an unknown value to match with
        $unknownValue = $this->unknownValue();

        if (($value = $this->get($key, $unknownValue)) !== $unknownValue) {
            return $value;
        }

        if (($value = $callback()) !== null) {
            return $this->set($key, $value, $ttl) ? $value : null;
        }

        return null;
    }

    /**
     * Fetches a value from the cache and then delete the item.
     *
     * @param string $key The unique key of this item in the cache.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function pull($key)
    {
        if ( ! is_string($key)) {
            throw new InvalidCacheKey('SimpleCache key must be a string');
        }

        // Create an unknown value to match with
        $unknownValue = $this->unknownValue();

        if (($value = $this->get($key, $unknownValue)) !== $unknownValue) {
            $this->delete($key);

            return $value;
        }

        return null;
    }

    /**
     * Create an random unknown value that can be used to determine if a cached value exists
     * @return string
     */
    private function unknownValue(): string
    {
        return base64_encode('unknownValue_' . time());
    }
}

