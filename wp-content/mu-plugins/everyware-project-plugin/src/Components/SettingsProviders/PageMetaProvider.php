<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components\SettingsProviders;

use Everyware\ProjectPlugin\Components\Contracts\SettingsProvider;
use Infomaker\Everyware\Base\Models\Page;

/**
 * Class MetaboxDB
 * @package Everyware\ProjectPlugin\Components\SettingsProviders
 */
class PageMetaProvider implements SettingsProvider
{
    public const VERSION_FIELD = 'current_version';

    /**
     * @var string
     */
    private $metaKey;

    /**
     * @var Page
     */
    private $page;

    /**
     * @var array
     */
    protected $fields;

    public function __construct($metaKey, Page $page, array $requiredFields = [])
    {
        $this->page = $page;
        $this->metaKey = $metaKey;
        $this->fields = $requiredFields;
    }

    /**
     * Retrieve stored settings
     *
     * @return array
     */
    public function get(): array
    {
        return $this->page->getMeta($this->metaKey);
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
        $settings = $this->get();
        if (array_key_exists($key, $settings)) {
            return $settings[$key];
        }

        return null;
    }

    /**
     * Save the settings
     *
     * @param array $settings
     *
     * @return bool Whether the save was successful or not
     */
    public function save(array $settings = []): bool
    {
        return $this->page->updateMeta($this->metaKey, $settings);
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
