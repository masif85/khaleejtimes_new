<?php declare(strict_types=1);


namespace Everyware\Plugin\RedirectOriginalUrls;


use Everyware\Plugin\RedirectOriginalUrls\Contracts\MigratedUrlRepositoryInterface;
use Everyware\Plugin\RedirectOriginalUrls\Exceptions\NoUrlSettingVariationException;

/**
 * Class OriginalUrlsRedirector
 * @package Everyware\Plugin\RedirectOriginalUrls
 */
class OriginalUrlsRedirector
{
    public function __construct(MigratedUrlRepositoryInterface $repository)
    {
        $this->repository = $repository;

        $this->handle404();
    }

    public function handle404(): void
    {
        if (!is_404()) {
            return;
        }

        $oldUrl = $this->getCurrentUrl();
        $newUrl = $this->repository->findNewUrl($oldUrl);

        if ($newUrl === null) {
            return;
        }

        wp_safe_redirect($newUrl, 301);
        header('Cache-Control: max-age=' . self::CACHE_CONTROL_MAX_AGE);
        exit;
    }

    private function getCurrentUrl(): string
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';

        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * @param string      $url
     * @param array       $urlSettings
     * @param string|null $domain
     *
     * @return string[]
     */
    public static function createUrlVariations(string $url, array $urlSettings, ?string $domain): array
    {
        $useDomain = ($urlSettings[PluginSettings::URL_SETTING_EXTENT] === PluginSettings::URL_SETTING_EXTENT_URLS);

            $parts = parse_url($url);
        if (!$useDomain) {
            $url = $parts['path'] ?? '/';
        }
        elseif ($domain !== null) {
            $parts['host'] = $domain;
            $url = self::reverseParseUrl($parts);
        }

        $urlVariations = [$url];

        self::varyTrailingSlash($urlVariations, $urlSettings[PluginSettings::URL_SETTING_TRAILING_SLASHES]);

        if ($useDomain) {

            self::varyScheme($urlVariations, $urlSettings[PluginSettings::URL_SETTING_SCHEMES]);

            self::varyWwwSubdomain($urlVariations, $urlSettings[PluginSettings::URL_SETTING_WWW_SUBDOMAINS]);
        }

        return $urlVariations;
    }

    /**
     * Make sure each URL in $urlVariations has an instance with each desired scheme.
     *
     * @param string[] $urlVariations
     * @param array    $setting
     *
     * @throws NoUrlSettingVariationException
     */
    public static function varyScheme(array &$urlVariations, array $setting): void
    {
        if (!in_array(true, $setting, true)) {
            throw new NoUrlSettingVariationException('No URL scheme defined.');
        }

        $result = [];
        foreach ($urlVariations as $url) {
            $parts = parse_url($url);
            foreach ($setting as $scheme => $enabled) {
                if (!$enabled) {
                    continue;
                }
                $parts['scheme'] = $scheme;
                $result[] = self::reverseParseUrl($parts);
            }
        }

        $urlVariations = array_unique($result);
    }

    /**
     * Make sure each URL in $urlVariations has an instance with and/or without 'www.' subdomain, depending on what $setting dictates.
     *
     * @param string[] $urlVariations
     * @param array    $setting
     */
    public static function varyWwwSubdomain(array &$urlVariations, array $setting): void
    {
        $doWith = $setting[PluginSettings::URL_SETTING_WWW_SUBDOMAINS_WITH];
        $doWithout = $setting[PluginSettings::URL_SETTING_WWW_SUBDOMAINS_WITHOUT];

        if (!$doWith && !$doWithout) {
            throw new NoUrlSettingVariationException('No www subdomain setting defined.');
        }

        $result = [];
        foreach ($urlVariations as $url) {
            $parts = parse_url($url);

            // By default, remove any 'www.' subdomain. Then consider putting it back.
            if (strpos($parts['host'], 'www.') === 0) {
                $parts['host'] = substr($parts['host'], 4);
            }

            if ($doWithout) {
                $result[] = self::reverseParseUrl($parts);
            }
            if ($doWith) {
                $parts['host'] = 'www.' . $parts['host'];
                $result[] = self::reverseParseUrl($parts);
            }
        }

        $urlVariations = array_unique($result);
    }

    /**
     * Make sure each URL in $urlVariations has an instance with and/or without trailing slash, depending on what $setting dictates.
     *
     * @param string[] $urlVariations
     * @param array    $setting
     */
    public static function varyTrailingSlash(array &$urlVariations, array $setting): void
    {
        $doWith = $setting[PluginSettings::URL_SETTING_TRAILING_SLASHES_WITH];
        $doWithout = $setting[PluginSettings::URL_SETTING_TRAILING_SLASHES_WITHOUT];

        if (!$doWith && !$doWithout) {
            throw new NoUrlSettingVariationException('No trailing slash setting defined.');
        }

        $result = [];
        foreach ($urlVariations as $url) {
            $parts = parse_url($url);

            // Start with a path without any trailing slash. Then consider whether to put it back.
            $parts['path'] = rtrim($parts['path'] ?? '', '/');

            if ($doWithout) {
                $result[] = self::reverseParseUrl($parts);
            }
            if ($doWith) {
                $parts['path'] .= '/';
                $result[] = self::reverseParseUrl($parts);
            }
        }

        $urlVariations = array_unique($result);
    }

    /**
     * Put together a URL from its components, reversing the effects of parse_url().
     *
     * @param array $parts Results of parse_url().
     *
     * @return string
     */
    private static function reverseParseUrl(array $parts): string
    {
        $result = (isset($parts['scheme'])) ? $parts['scheme'] . '://' : '';

        if (isset($parts['user'])) {
            $result .= $parts['user'];
            $result .= (isset($parts['pass'])) ? ':' . $parts['pass'] : '';
            $result .= '@';
        }

        $result .= (isset($parts['host'])) ? $parts['host'] : '';
        $result .= (isset($parts['port'])) ? ':' . $parts['port'] : '';
        $result .= (isset($parts['path'])) ? $parts['path'] : '';
        $result .= (isset($parts['query'])) ? '?' . $parts['query'] : '';
        $result .= (isset($parts['fragment'])) ? '#' . $parts['fragment'] : '';

        return $result;
    }

    /**
     * Default cache time for redirects, in seconds.
     */
    private const CACHE_CONTROL_MAX_AGE = 3600;

    /**
     * @var MigratedUrlRepositoryInterface
     */
    private $repository;

}
