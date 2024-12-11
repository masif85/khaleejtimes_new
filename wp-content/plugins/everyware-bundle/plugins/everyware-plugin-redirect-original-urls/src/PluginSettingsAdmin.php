<?php declare(strict_types=1);

namespace Everyware\Plugin\RedirectOriginalUrls;

use Everyware\ProjectPlugin\Components\ComponentAdmin;
use Everyware\ProjectPlugin\Helpers\Message;
use Everyware\ProjectPlugin\Helpers\MessageBag;
use ValueError;

/**
 * Class PluginSettingsAdmin
 * @package Everyware\Plugin\RedirectOriginalUrls
 */
class PluginSettingsAdmin extends ComponentAdmin
{
    private $checkboxGroups = [
        PluginSettings::URL_SETTING_SCHEMES,
        PluginSettings::URL_SETTING_WWW_SUBDOMAINS,
        PluginSettings::URL_SETTING_TRAILING_SLASHES
    ];

    /**
     * Prepare domains array to be edited in form.
     *
     * @param array $domains
     *
     * @return string
     */
    public static function formatDomains(array $domains): string
    {
        return implode(', ', $domains);
    }

    /**
     * Parse domains from form input.
     *
     * @param string $domains
     *
     * @return array
     */
    public static function parseDomains(string $domains): array
    {
        $result = explode(',', trim($domains, ' ,'));
        $result = array_map('trim', $result);
        return array_values(array_filter($result, static function ($value) {
            return !empty($value);
        }));
    }

    public function create(array $storedData): string
    {
        return parent::edit($storedData);
    }

    public function edit(array $storedData): string
    {
        $storedData = $this->formatData($storedData);

        return parent::edit($storedData);
    }

    public function update(array $newData, array $oldData = []): bool
    {
        $newData = $this->parseData($newData, $oldData);

        // On validation error, add an error message and refuse to update.
        try {
            $this->validate($newData, $oldData);
        } catch (ValueError $e) {
            $message = new Message(
                Message::ERROR,
                '<strong>' . __('Failed to save', REDIRECT_ORIGINAL_URLS_TEXT_DOMAIN) . ':</strong> ' . $e->getMessage()
            );
            MessageBag::add($message);

            return false;
        }

        return parent::update($newData, $oldData);
    }

    public function store(array $newData, array $oldData = []): bool
    {
        return parent::update($newData, $oldData);
    }

    /**
     * Transform data from stored format to format that can be used by the form.
     *
     * Opposite of {@see PluginSettingsAdmin::parseData()}.
     *
     * @param array $storedData
     *
     * @return array
     */
    public function formatData(array $storedData): array
    {
        $storedData[PluginSettings::URL_SETTING_DOMAINS] = self::formatDomains($storedData[PluginSettings::URL_SETTING_DOMAINS]);

        // Turn array elements into separate parameters, since current checkbox implementation can't handle arrays.
        foreach ($this->checkboxGroups as $settingKey) {
            foreach ($storedData[$settingKey] as $option => $checked) {
                $storedData[$settingKey . '_' . $option] = $checked;
            }
            unset($storedData[$settingKey]);
        }

        return $storedData;
    }

    /**
     * Transform data from form input to format that can be stored.
     *
     * Opposite of {@see PluginSettingsAdmin::formatData()}.
     *
     * @param array $newData
     * @param array $oldData
     *
     * @return array
     */
    public function parseData(array $newData, array $oldData = []): array
    {
        $newData[PluginSettings::URL_SETTING_DOMAINS] = self::parseDomains($newData[PluginSettings::URL_SETTING_DOMAINS]);

        foreach ($this->checkboxGroups as $settingKey) {
            $newData[$settingKey] = [];
            foreach (PluginSettings::$fields[$settingKey] as $option => $checked) {
                $newData[$settingKey][$option] = filter_var($newData[$settingKey . '_' . $option] ?? '', FILTER_VALIDATE_BOOL);
                unset($newData[$settingKey . '_' . $option]);
            }
        }

        return $newData;
    }

    /**
     * @param array $newData
     * @param array $oldData
     *
     * @throws ValueError If $newData does not validate.
     */
    public function validate(array $newData, array $oldData = []): void
    {
        if (!in_array(true, $newData[PluginSettings::URL_SETTING_TRAILING_SLASHES], true)) {
            throw new ValueError(__('Select whether URLs end with a trailing slash or not.', REDIRECT_ORIGINAL_URLS_TEXT_DOMAIN));
        }

        if ($newData[PluginSettings::URL_SETTING_EXTENT] === PluginSettings::URL_SETTING_EXTENT_PATHS) {
            // The rest of the validations are not relevant.
            return;
        }

        if (count($newData[PluginSettings::URL_SETTING_DOMAINS]) === 0) {
            throw new ValueError(__('Enter at least one domain.', REDIRECT_ORIGINAL_URLS_TEXT_DOMAIN));
        }

        foreach ($newData[PluginSettings::URL_SETTING_DOMAINS] as $domain) {
            if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                throw new ValueError(sprintf(__('%s is not a valid domain name.', REDIRECT_ORIGINAL_URLS_TEXT_DOMAIN), $domain));
            }
        }

        if (!in_array(true, $newData[PluginSettings::URL_SETTING_SCHEMES], true)) {
            throw new ValueError(__('Select at least one scheme.', REDIRECT_ORIGINAL_URLS_TEXT_DOMAIN));
        }

        if (!in_array(true, $newData[PluginSettings::URL_SETTING_WWW_SUBDOMAINS], true)) {
            throw new ValueError(__('Select whether www. subdomains are used or not.', REDIRECT_ORIGINAL_URLS_TEXT_DOMAIN));
        }
    }
}
