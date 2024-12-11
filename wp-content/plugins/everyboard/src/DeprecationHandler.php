<?php declare(strict_types=1);

namespace Everyware\Everyboard;

/**
 * Class DeprecationHandler
 * @package Everyware\Everyboard
 */
class DeprecationHandler
{
    public static function log(string $message): void
    {
        error_log($message);
    }

    public static function error(string $message): void
    {
        trigger_error($message, E_USER_DEPRECATED);
    }
}
