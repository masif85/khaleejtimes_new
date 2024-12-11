<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Helpers;

class WordPressPageHelper
{
    public static function onPluginsPage(): bool
    {
        global $pagenow;

        return $pagenow === 'plugins.php';
    }
}
