<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components\Contracts;

interface SettingsRepository
{
    public function addRequiredField($key, $defaultValue): void;

    /**
     * Retrieve stored settings
     *
     * @return array
     */
    public function get(): array;

    public function getValidSettings(array $settings = []): array;

    /**
     * Retrieve single value from the stored settings
     *
     * @param $key
     *
     * @return mixed Will return null if key dose not exist
     */
    public function getValue($key);

    /**
     * Save the settings
     *
     * @param array $settings
     *
     * @return bool Whether the save was successful or not
     */
    public function save(array $settings = []): bool;

    /**
     * Try to store settings
     *
     * @param array $settings
     *
     * @return bool Whether the save was successful or not
     */
    public function store(array $settings = []): bool;

    /**
     * Try to updated settings
     *
     * @param array $newSettings
     * @param array $oldSettings
     *
     * @return bool Whether the save was successful or not
     */
    public function update(array $newSettings, array $oldSettings = []): bool;
}
