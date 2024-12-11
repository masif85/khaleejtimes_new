<?php declare(strict_types=1);


namespace Everyware\Plugin\RedirectOriginalUrls;

use Everyware\Plugin\RedirectOriginalUrls\Contracts\MigratedUrlRepositoryInterface;
use Everyware\Plugin\RedirectOriginalUrls\Exceptions\OcSearchFailedException;
use Everyware\Plugin\RedirectOriginalUrls\Exceptions\UnexpectedUrlExtentException;
use Everyware\ProjectPlugin\Components\Contracts\SettingsRepository;
use OcAPI;
use OcArticle;
use Unit\Everyware\Plugin\RedirectOriginalUrls\FakeOcAPI;
use Unit\Everyware\Plugin\RedirectOriginalUrls\FakeOcArticle;

/**
 * Class OcMigratedUrlRepository
 *
 * A resource that tells where old URL:s have migrated to.
 *
 * @package Everyware\Plugin\RedirectOriginalUrls
 */
class OcMigratedUrlRepository implements MigratedUrlRepositoryInterface
{
    /**
     * OcMigratedUrlRepository constructor.
     *
     * @param OcAPI|FakeOcAPI $ocApi
     *
     * @todo Replace with OcApiInterface once boths classes implement it.
     */
    public function __construct(object $ocApi)
    {
        $this->ocApi = $ocApi;
    }

    /**
     * @param string $oldUrl
     *
     * @return string|null
     */
    public function findNewUrl(string $oldUrl): ?string
    {
        $groupedUrlVariations = $this->createGroupedUrlVariations($oldUrl);

        // It is much faster to query all the URLs in a single search, than to search them one by one.
        $allUrlVariations = [];
        foreach ($groupedUrlVariations as $urlVariations) {
            $allUrlVariations = array_merge($allUrlVariations, $urlVariations);
        }
        $articles = $this->search($allUrlVariations);

        if (count($articles) === 0) {
            return null;
        }

        if (count($articles) === 1) {
            return $articles[0]->get_permalink();
        }

        // In the rare event that we got more than 1 article, be sure to pick the one that matches the group with the highest precedence.
        $propName = $this->getUrlSettings()[PluginSettings::ORIGINAL_URLS_PROPERTY_NAME];
        foreach ($groupedUrlVariations as $urlVariations) {
            foreach ($articles as $article) {
                if (count(array_intersect($urlVariations, $article->get($propName))) > 0) {
                    return $article->get_permalink();
                }
            }
        }
    }

    /**
     * @param string[] $urlVariations
     *
     * @return OcArticle[]|FakeOcArticle[]
     */
    private function search(array $urlVariations): array
    {
        $useCache = false;
        $createArticle = false;
        $propName = $this->getUrlSettings()[PluginSettings::ORIGINAL_URLS_PROPERTY_NAME];
        $params = [
            'q'            => $propName . ':("' . implode('" OR "', $urlVariations) . '")',
            'properties'   => ['uuid', 'contenttype'],
            'contenttypes' => ['Article']
        ];

        $result = $this->ocApi->search($params, $useCache, $createArticle);

        if (count($result) === 0) {
            throw new OcSearchFailedException('Failed Article search with query "' . $params['q'] . '"');
        }

        $articles = array_filter($result, static function ($item) {
            // todo Replace with OcApiInterface once boths classes implement it.
            return ($item instanceof OcArticle || $item instanceof FakeOcArticle);
        });

        foreach ($articles as $i => $hit) {
            $articles[$i] = $this->ocApi->get_single_object($hit->get_value('uuid'));
        }

        return $articles;
    }

    /**
     * Override the default settings.
     *
     * @param SettingsRepository $urlSettings
     */
    public function setUrlSettings(SettingsRepository $urlSettings)
    {
        $this->urlSettings = $urlSettings;
    }

    /**
     * @return array
     */
    private function getUrlSettings(): array
    {
        $settings = ($this->urlSettings !== null) ? $this->urlSettings->get() : PluginSettings::create()->get();

        // Default to current domain, if none is set.
        if (count($settings[PluginSettings::URL_SETTING_DOMAINS]) === 0) {
            $settings[PluginSettings::URL_SETTING_DOMAINS] = [$_SERVER['HTTP_HOST']];
        }

        return $settings;
    }

    /**
     * Create groups of URL variations, with groups ordered by precedence (in the order you would like to do searches).
     *
     * @param string $url
     *
     * @return array  Grouped by second-level domain, if applicable. [ [url, url, url, ...], ... ]
     * @throws UnexpectedUrlExtentException
     */
    private function createGroupedUrlVariations(string $url): array
    {
        $urlVariations = [];

        $urlSettings = $this->getUrlSettings();

        switch ($urlSettings[PluginSettings::URL_SETTING_EXTENT]) {
            case PluginSettings::URL_SETTING_EXTENT_PATHS:
                $urlVariations[] = OriginalUrlsRedirector::createUrlVariations($url, $urlSettings, null);
                break;
            case PluginSettings::URL_SETTING_EXTENT_URLS:
                $this->addUrlDomain($url, $urlSettings[PluginSettings::URL_SETTING_DOMAINS]);
                foreach ($urlSettings[PluginSettings::URL_SETTING_DOMAINS] as $domain) {
                    $urlVariations[] = OriginalUrlsRedirector::createUrlVariations($url, $urlSettings, $domain);
                }
                break;
            default:
                throw new UnexpectedUrlExtentException('Unknown URL extent `' . $urlSettings[PluginSettings::URL_SETTING_EXTENT] . '`');
        }

        return $urlVariations;
    }

    /**
     * Make sure that $domains begins with the domain of $url.
     *
     * @param string $url
     * @param array  $domains
     */
    private function addUrlDomain(string $url, array &$domains): void
    {
        $parts = parse_url($url);
        if (!isset($parts['host'])) {
            return;
        }
        $domains = array_unique(array_merge([$parts['host']], $domains));
    }

    /**
     * @var OcAPI|FakeOcAPI
     * @todo Replace with OcApiInterface once boths classes implement it.
     */
    private $ocApi;

    /**
     * @var SettingsRepository|null
     */
    private $urlSettings;
}
