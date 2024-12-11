<?php declare(strict_types=1);


namespace Everyware\RssFeeds;


use Everyware\ProjectPlugin\Components\SettingsHandler;
use Infomaker\Everyware\Twig\View;

/**
 * Class ItemSettings
 *
 * Handles the metabox to admin a feed item's settings.
 *
 * @package Everyware\RssFeeds
 */
class ItemSettings extends RssFeedsMetabox
{
    public static $postmetaId = 'rss-item-settings';

    protected $metaBoxId = 'rss-item-settings';

    /**
     * Contains the fields of the metabox form.
     *
     * @var array $fields
     *     ['has_title']        bool
     *     ['has_description']  bool
     *     ['has_dc_creator']   bool
     *     ['has_pub_date']     bool
     *     ['has_category']     bool
     *     ['has_image']        bool
     *     ['image_width']      int    Width in pixels.
     *     ['has_media_credit'] bool
     */
    protected $fields = [
        'has_title' => true,
        'has_description' => true,
        'has_dc_creator' => true,
        'has_pub_date' => true,
        'has_category' => true,
        'has_image' => true,
        'image_width' => 500,
        'has_media_credit' => true
    ];

    protected $version = '1.0.0';

    /**
     * @var string[] All the keys in self::fields that are rendered as checkboxes.
     */
    private $checkbox_fields = [
        'has_title',
        'has_description',
        'has_dc_creator',
        'has_pub_date',
        'has_category',
        'has_image',
        'has_media_credit'
    ];

    /**
     * Function required by the metabox-child. Returns the string-representation of the metabox-form
     *
     * @param array $formSettings
     * @param array $storedData
     *
     * @return string
     */
    protected function formContent(array $formSettings, array $storedData): string
    {
        $this->prepareForForm($storedData);

        $settingsHandler = (new SettingsHandler($this->metaInputPrefix));
        $formData = $settingsHandler->generateFormData($storedData);

        return View::generate($this->templatePath . 'item-settings', array_replace($formData, [
            'settings' => $formSettings,
            'form' => $this->formBuilder
        ]));
    }

    /**
     * Fires on saving updated metabox
     *
     * @param array $newInstance
     * @param array $oldInstance
     *
     * @return array $newInstance The form data to be saved
     */
    protected function onChange(array $newInstance, array $oldInstance = []): array
    {
        $this->cleanUpFormData($newInstance);

        return $newInstance;
    }

    /**
     * Fires on saving new metabox
     *
     * @param array $newInstance
     *
     * @return array $newInstance The form data to be saved
     */
    protected function onStore($newInstance): array
    {
        $this->cleanUpFormData($newInstance);

        return $newInstance;
    }

    /**
     * Prepare form data for being rendered in the form.
     *
     * @param array $storedData
     */
    private function prepareForForm(array &$storedData): void
    {
        // Convert all values intended for checkboxes from true/false to 'on'/'off'.
        foreach ($this->checkbox_fields as $key) {
            $storedData[$key] = ($storedData[$key] === true) ? 'on' : 'off';
        }
    }

    /**
     * Clean up form data before saving.
     *
     * @param array $data
     */
    private function cleanUpFormData(array &$data): void
    {
        // Convert all checkbox values from 'on'/'off' to true/false.
        foreach ($this->checkbox_fields as $key) {
            $data[$key] = ($data[$key] === 'on');
        }

        if ($data['has_image']) {
            $data['image_width'] = (int)$data['image_width'];
        }
    }
}
