<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components\SettingsProviders;

use Everyware\ProjectPlugin\Components\Contracts\SettingsProvider;

/**
 * Class WidgetDB
 * @package Everyware\ProjectPlugin\Components\SettingsProviders
 */
class SimpleSettingsProvider implements SettingsProvider
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @var array
     */
    protected $settings = [];

    public function __construct(array $requiredFields = [])
    {
        $this->fields = $requiredFields;
    }

    /**
     * Save the settings
     *
     * @param array $settings
     *
     * @return bool
     */
    public function save(array $settings = []): bool
    {
        $this->settings = $settings;

        return true;
    }

    /**
     * Retrieve stored settings
     *
     * @return array
     */
    public function get(): array
    {
        return $this->settings;
    }

    /**
     * Retrieve single value from the stored settings
     *
     * @param $key
     *
     * @return mixed Will return null if key dose not exist
     */
    public function getSingle($key)
    {
        if (array_key_exists($key, $this->settings)) {
            return $this->settings[$key];
        }

        return null;
    }

    /**
     * Retrieve the fields that the provider requires to be set with a proposed default value.
     * ex. [ fieldName => defaultValue ]
     *
     * @return array
     */
    public function requiredFields(): array
    {
        return $this->fields;
    }
}
