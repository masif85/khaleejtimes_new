<?php declare(strict_types=1);
// Setup Environment variables for use of Dates
putenv('PHP_TIMEZONE=Europe/Stockholm');
putenv('PHP_DEFAULT_LOCALE=sv-SE');

if ( ! defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
}

if ( ! defined('PHP_OB_CACHE_TTL')) {
    define('PHP_OB_CACHE_TTL', 10);
}

if ( ! defined('MINUTE_IN_SECONDS')) {
    define('MINUTE_IN_SECONDS', 60);
    define('HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS);
    define('DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS);
    define('WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS);
    define('MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS);
    define('YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS);
}
