<?php declare(strict_types=1);


namespace Unit\Everyware\Plugin\RedirectOriginalUrls;

use Everyware\Plugin\RedirectOriginalUrls\PluginSettings;

/**
 * Class FakePluginSettings
 *
 * Is initialized with data instead of loading it from a DB. Cannot save the data anywhere.
 *
 * @package Unit\Everyware\Plugin\RedirectOriginalUrls
 */
class FakePluginSettings extends PluginSettings
{
    public static function create(array $settings = []): PluginSettings
    {
        return new parent(new FakeSettingsProvider($settings));
    }
}
