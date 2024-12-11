<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components\Contracts;

/**
 * Trait SettingsDB
 * @package Everyware\ProjectPlugin\Components\Contracts
 */
interface SettingsProvider
{
    /**
     * Retrieve stored settings
     *
     * @return array
     */
    public function get(): array;

    /**
     * Retrieve single value from the stored settings
     *
     * @param $key
     *
     * @return mixed Will return null if key dose not exist
     */
    public function getSingle($key);

    /**
     * Save the settings
     *
     * @param array $settings
     *
     * @return bool Whether the save was successful or not
     */
    public function save(array $settings = []): bool;

    /**
     * Retrieve the fields that the provider requires to be set with a proposed default value.
     * ex. [ fieldName => defaultValue ]
     *
     * @return array
     */
    public function requiredFields(): array;
}
