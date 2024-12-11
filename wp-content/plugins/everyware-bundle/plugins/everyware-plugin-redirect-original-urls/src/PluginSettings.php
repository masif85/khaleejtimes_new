<?php declare(strict_types=1);

namespace Everyware\Plugin\RedirectOriginalUrls;

use Everyware\ProjectPlugin\Components\ComponentSettingsRepository;
use Everyware\ProjectPlugin\Components\SettingsProviders\CollectionDbProvider;
use Infomaker\Everyware\Support\Storage\CollectionDB;

/**
 * Class PluginSettings
 * @package Everyware\Plugin\RedirectOriginalUrls
 */
class PluginSettings extends ComponentSettingsRepository
{
    public const OPTION_NAME = 'ew_redirect_original_urls_settings';

    /**
     * What the OriginalUrls property is named in OC.
     */
    public const ORIGINAL_URLS_PROPERTY_NAME = 'original_urls_property_name';

    /**
     * Setting name and valid values for URL extent.
     */
    public const URL_SETTING_EXTENT = 'extent';
    public const URL_SETTING_EXTENT_URLS = 'urls';
    public const URL_SETTING_EXTENT_PATHS = 'paths';

    /**
     * Setting name and valid values for schemes used.
     */
    public const URL_SETTING_SCHEMES = 'schemes';
    public const URL_SETTING_SCHEME_HTTPS = 'https';
    public const URL_SETTING_SCHEME_HTTP = 'http';

    /**
     * Setting name for domains used.
     */
    public const URL_SETTING_DOMAINS = 'domains';

    /**
     * Setting name and valid values for whether the URLs are referred to using "www." subdomains.
     */
    public const URL_SETTING_WWW_SUBDOMAINS = 'www_subdomains';
    public const URL_SETTING_WWW_SUBDOMAINS_WITH = 'with';
    public const URL_SETTING_WWW_SUBDOMAINS_WITHOUT = 'without';

    /**
     * Setting name and valid values for whether the URLs have trailing slashes.
     */
    public const URL_SETTING_TRAILING_SLASHES = 'trailing_slashes';
    public const URL_SETTING_TRAILING_SLASHES_WITH = 'with';
    public const URL_SETTING_TRAILING_SLASHES_WITHOUT = 'without';

    public static $fields = [
        self::ORIGINAL_URLS_PROPERTY_NAME  => 'OriginalUrls',
        self::URL_SETTING_EXTENT           => self::URL_SETTING_EXTENT_URLS,
        self::URL_SETTING_DOMAINS          => [],
        self::URL_SETTING_SCHEMES          => [
            self::URL_SETTING_SCHEME_HTTPS => true,
            self::URL_SETTING_SCHEME_HTTP  => true
        ],
        self::URL_SETTING_WWW_SUBDOMAINS   => [
            self::URL_SETTING_WWW_SUBDOMAINS_WITH    => true,
            self::URL_SETTING_WWW_SUBDOMAINS_WITHOUT => true
        ],
        self::URL_SETTING_TRAILING_SLASHES => [
            self::URL_SETTING_TRAILING_SLASHES_WITH    => true,
            self::URL_SETTING_TRAILING_SLASHES_WITHOUT => true
        ]
    ];

    /**
     * @return PluginSettings
     */
    public static function create(): PluginSettings
    {
        return new static(new CollectionDbProvider(new CollectionDB(static::OPTION_NAME), static::$fields));
    }
}

