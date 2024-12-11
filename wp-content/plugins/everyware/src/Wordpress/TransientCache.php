<?php declare(strict_types=1);

namespace Everyware\Wordpress;

/**
 * Class Cache
 * @package Everyware\Wordpress
 */
class TransientCache
{
    public function get($key)
    {
        return get_transient($key);
    }

    public function set($key, $value, $ttl = 0)
    {
        return set_transient($key, $value, $ttl);
    }

    public function delete($key)
    {
        return delete_transient($key);
    }
}
