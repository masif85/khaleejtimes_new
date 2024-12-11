<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components\SettingsProviders;

use Everyware\ProjectPlugin\Components\Contracts\SettingsProvider;
use Infomaker\Everyware\Support\Storage\CollectionDB;

/**
 * Class PluginSettingsDB
 * @package Everyware\ProjectPlugin\Components\SettingsProviders
 */
class CollectionDbProvider implements SettingsProvider
{
    /**
     * @var CollectionDB
     */
    protected $db;

    /**
     * @var array
     */
    protected $fields;

    /**
     * PluginSettingsDB constructor.
     *
     * @param CollectionDB $db
     * @param array        $requiredFields
     */
    public function __construct(CollectionDB $db, array $requiredFields = [])
    {
        $this->db = $db;
        $this->fields = $requiredFields;
    }

    /**
     * Retrieve stored settings
     *
     * @return array
     */
    public function get(): array
    {
        return $this->db->all()->toArray();
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
        return $this->db->all()->get($key);
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
        return $this->db->setCollection($settings)->save();
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
