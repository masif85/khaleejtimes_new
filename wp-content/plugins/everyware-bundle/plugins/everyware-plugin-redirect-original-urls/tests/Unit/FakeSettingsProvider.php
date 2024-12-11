<?php declare(strict_types=1);


namespace Unit\Everyware\Plugin\RedirectOriginalUrls;

use Everyware\Plugin\RedirectOriginalUrls\PluginSettings;
use Everyware\ProjectPlugin\Components\Contracts\SettingsProvider;

/**
 * Class FakeSettingsProvider
 *
 * Can be initialized with data without loading it from a DB. Cannot save the data anywhere.
 *
 * @package Unit\Everyware\Plugin\RedirectOriginalUrls
 */
class FakeSettingsProvider implements SettingsProvider
{
    private $settings;

    public function __construct(array $settingChanges = [])
    {
        $this->settings = array_replace_recursive($this->requiredFields(), $settingChanges);
    }

    public function get(): array
    {
        return $this->settings;
    }

    public function getSingle($key): ?array
    {
        return $this->settings[$key] ?? null;
    }

    public function save(array $settings = []): bool
    {
        return false;
    }

    public function requiredFields(): array
    {
        return PluginSettings::$fields;
    }
}
