<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components;

use Everyware\ProjectPlugin\Components\Contracts\SettingsProvider;
use Everyware\ProjectPlugin\Components\Contracts\SettingsRepository;

/**
 * Class ComponentSettingsRepository
 * @package Everyware\ProjectPlugin\Components
 */
class ComponentSettingsRepository implements SettingsRepository
{
    /**
     * @var SettingsProvider
     */
    protected $provider;

    /**
     * @var array
     */
    protected $requiredFields;

    /**
     * ComponentSettingsRepository constructor.
     *
     * @param SettingsProvider $provider
     */
    public function __construct(SettingsProvider $provider)
    {
        $this->provider = $provider;
        $this->requiredFields = $provider->requiredFields();
    }

    public function addRequiredField($key, $defaultValue): void
    {
        $this->requiredFields[$key] = $defaultValue;
    }

    /**
     * Retrieve stored settings
     *
     * @return array
     */
    public function get(): array
    {
        return array_replace($this->requiredFields, $this->provider->get());
    }

    /**
     * Retrieve single value from the stored settings
     *
     * @param $key
     *
     * @return mixed Will return null if key dose not exist
     */
    public function getValue($key)
    {
        return $this->provider->getSingle($key);
    }

    public function getValidSettings(array $settings = []): array
    {
        $validFields = [];

        foreach ($settings as $name => $value) {
            if (array_key_exists($name, $this->requiredFields)) {
                $validFields[$name] = $value;
            }
        }

        return array_replace($this->requiredFields, $validFields);
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
        if (empty($settings)) {
            return false;
        }

        return $this->provider->save($this->getValidSettings($settings));
    }

    /**
     * Try to store settings
     *
     * @param array $settings
     *
     * @return bool Whether the save was successful or not
     */
    public function store(array $settings = []): bool
    {
        return $this->save($settings);
    }

    /**
     * Try to updated settings
     *
     * @param array $newSettings
     * @param array $oldSettings
     *
     * @return bool Whether the save was successful or not
     */
    public function update(array $newSettings, array $oldSettings = []): bool
    {
        return $this->save(array_replace($oldSettings, $this->getValidSettings($newSettings)));
    }
}
