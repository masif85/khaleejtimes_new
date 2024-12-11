<?php declare(strict_types=1);

/**
 * =======================================
 * Redis Object Cache plugin configuration
 * =======================================
 *
 * Available Beanstalk Variables:
 * - REDIS_OB_DEBUG (boolean)
 * - REDIS_OB_DISABLED (boolean)
 * - REDIS_OB_HOST (string)
 * - REDIS_OB_MAXTTL (string)
 * - REDIS_OB_PORT (string)
 * - REDIS_OB_PREFIX (string)
 *
 * "REDIS_OB_PREFIX" = Previously known in version 1.x as CACHE_KEY_SALT
 * @links https://wordpress.org/plugins/redis-cache
 * @links https://github.com/rhubarbgroup/redis-cache/wiki/
 */

/**
 * =====================
 * Configuration Options
 * =====================
 */

// Backwards compatibility: map `CACHE_KEY_SALT` variable to `REDIS_OB_PREFIX`.
$redisPrefix = env('REDIS_OB_PREFIX', env('CACHE_KEY_SALT', $_SERVER['IMHOST']));

// Set the prefix for all cache keys.
define('WP_REDIS_PREFIX', $redisPrefix);

// Specifies the client used to communicate with Redis.
define('WP_REDIS_CLIENT', 'phpredis');

// Disable banners and admin notices that promote "Redis Cache Pro".
define('WP_REDIS_DISABLE_BANNERS', true);

// Disable the HTML footer comment and it's optional debugging information when WP_DEBUG is enabled.
define('WP_REDIS_DISABLE_COMMENT', true);

// Disable collection of object cache metrics, such as hit rate total calls.
define('WP_REDIS_DISABLE_METRICS', true);

// Set maximum time-to-live (in seconds) for cache keys with an expiration time of 0. Default: 3h
define('WP_REDIS_MAXTTL', env('REDIS_OB_MAXTTL', '10800'));

// Disable the object cache.
if (filter_var(env('REDIS_OB_DISABLED', false), FILTER_VALIDATE_BOOLEAN)) {
    define('WP_REDIS_DISABLED', true);
}

// Disable graceful failures and throw exceptions.
if (filter_var(env('REDIS_OB_DEBUG', false), FILTER_VALIDATE_BOOLEAN)) {
    define('WP_REDIS_GRACEFUL', false);
}

/**
 * ========================
 * Configuration Parameters
 * ========================
 */

// IP or hostname of the target server. This is ignored when connecting to Redis using UNIX domain sockets.
define('WP_REDIS_HOST', env('REDIS_OB_HOST', 'redis'));

// TCP/IP port of the target server. This is ignored when connecting to Redis using UNIX domain sockets.
define('WP_REDIS_PORT', env('REDIS_OB_PORT', '6379'));
