<?php declare(strict_types=1);

namespace Everyware\Plugin\GoogleAnalytics;

use Everyware\Plugin\GoogleAnalytics\Models\Credentials;
use Everyware\ProjectPlugin\Components\ComponentSettingsRepository;
use Everyware\ProjectPlugin\Components\SettingsProviders\CollectionDbProvider;
use Infomaker\Everyware\Support\Storage\CollectionDB;
use Infomaker\Everyware\Support\Str;

/**
 * Class SettingsDB
 * @package Everyware\Plugin\GoogleAnalytics
 */
class PluginSettings extends ComponentSettingsRepository
{
    public const OPTION_NAME = 'ew_google_analytics_settings';

    private static $fields = [
        'credentials' => [],
        'time_start' => 'yesterday',
        'time_stop' => 'today',
        'measurement_id' => '',
        'property_id' => ''
    ];

    public function getCredentials(): Credentials
    {
        return new Credentials($this->getValue('credentials') ?? []);
    }

    public function getDateRange(): array
    {
        return [
            'start_date' => $this->getValue('time_start'),
            'end_date' => $this->getValue('time_stop')
        ];
    }

    public function getMeasurementId()
    {
        return $this->getValue('measurement_id') ?? '';
    }

    public function getPropertyId()
    {
        return $this->getValue('property_id');
    }

    public function hasMeasurementId(): bool
    {
        return Str::notEmpty($this->getMeasurementId());
    }

    public function updateCredentials(array $credentials = []): bool
    {
        $settings = $this->get();
        $settings['credentials'] = (new Credentials($credentials))->toArray();

        return $this->save($settings);
    }

    public static function create()
    {
        return new static(new CollectionDbProvider(new CollectionDB(static::OPTION_NAME), static::$fields));
    }
}
