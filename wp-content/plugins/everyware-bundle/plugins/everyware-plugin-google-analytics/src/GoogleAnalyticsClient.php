<?php
declare(strict_types=1);

namespace Everyware\Plugin\GoogleAnalytics;

use Everyware\Plugin\GoogleAnalytics\Models\Credentials;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Support\NewRelicLog;
use Infomaker\Everyware\Support\Storage\Cache;
use Infomaker\Everyware\Support\Str;
use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\Analytics\Data\V1beta\Filter\StringFilter\MatchType;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Protobuf\Internal\RepeatedField;

class GoogleAnalyticsClient
{

    /**
     * @var string
     */
    private const NOT_VALID_CREDENTIALS = 'not_valid_credentials';

    /**
     * Cache interval in minutes.
     * @var int
     */
    private const GA_CACHE_INTERVAL = 10;

    /**
     * @var string
     */
    private const GA_CACHE_KEY = 'ew_fetch_google_analytics';

    /**
     * @var string
     */
    private const GA_VALID_CREDENTIALS = 'ew_valid_ga_credentials';

    /**
     * The maximum number of articles from Google Analytics.
     *
     * @var integer
     */
    private static $limit = 30;

    /** @var SettingsTab */
    private $settings;

    /**
     * Contains custom settings
     *
     * @var array
     */
    private $customSettings = [];

    /** @var string */
    private $cacheKey;

    public $matchTypes = [
        MatchType::EXACT,
        MatchType::BEGINS_WITH,
        MatchType::ENDS_WITH,
        MatchType::CONTAINS,
        MatchType::FULL_REGEXP,
        MatchType::PARTIAL_REGEXP,
    ];

    /**
     * GoogleAnalyticsClient constructor.
     *
     * @param PluginSettings $settings
     */
    public function __construct(PluginSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Authorizes to Google Analytics.
     *
     * @param Credentials $credentials
     *
     * @return BetaAnalyticsDataClient
     */
    private function getAuthorizedClient(Credentials $credentials): ?BetaAnalyticsDataClient
    {
        try {
            return new BetaAnalyticsDataClient([
                'credentials' => $credentials->toArray()
            ]);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getGaData(): array
    {
        $credentials = $this->getCredentials();

        if ( ! $this->hasPropertyId() || ! $this->isValidCredentials($credentials)) {
            return [];
        }

        $client = $this->getAuthorizedClient($credentials);

        if ( ! $client instanceof BetaAnalyticsDataClient) {
            $this->invalidateCredentials($credentials);

            return [];
        }

        // Get data from GA
        $gaData = $this->getAnalytics($client);

        if ( ! $gaData instanceof RepeatedField) {
            return [];
        }

        $urls = [];

        foreach ($gaData as $key => $row) {
            $dimensionValues = $row->getDimensionValues();
            $field = $dimensionValues[0] ?? null;
            $url = $field ? $field->getValue() : null;

            $metricValues = $row->getMetricValues();
            $value = $metricValues[0] ?? null;
            $count = $value ? $value->getValue() : null;

            $urls[$url] = $count;
        }

        return $urls;
    }

    /**
     * Initiates the data fetching.
     *
     * @return array
     */
    public function fetchData(): array
    {
        $gaData = $this->getGaData();

        // Extract uuids from GA data
        $uuids = $this->extractUuids($gaData);

        // Get articles and store result
        $articles = (\count($uuids) === 0)
            ? []
            : $this->getArticleByUuids($uuids);

        $this->storeData($articles);

        return $articles;
    }

    /**
     * Get articles based of uuids
     *
     * @param array $uuids
     *
     * @return array
     */
    private function getArticleByUuids(array $uuids): array
    {
        $uuids = \array_unique($uuids, \SORT_REGULAR);

        $query = QueryBuilder::where('uuid', $uuids);

        $provider = OpenContentProvider::setup([
            'q' => $query->buildQueryString(),
            'contenttypes' => ['Article'],
            'limit' => \count($uuids),
        ]);
        $articles = $provider->queryWithRequirements();

        return $this->sortArticlesAfterUuidArray($uuids, $articles);
    }

    /**
     * @param array $uuids
     * @param       $articles
     *
     * @return array
     */
    private function sortArticlesAfterUuidArray(array $uuids, $articles): array
    {
        $sortedArticles = [];
        /** @var \OcArticle $article */
        foreach ($articles as $article) {
            /** @var int|string $position */
            $position = \array_search($article->get_value('uuid'), $uuids, true);
            if ($position === false) {
                continue;
            }
            $sortedArticles[$position] = $article;
        }

        \ksort($sortedArticles);

        return $sortedArticles;
    }

    /**
     * Extract uuids from urls
     *
     * @param array $urls
     *
     * @return array
     */
    private function extractUuids(array $urls): array
    {
        $uuids = [];
        $oldUrls = [];

        foreach ($urls as $url => $count) {
            $postId = \url_to_postid($url);

            // If post id is 0 we found nothing and have to check for old url in OC
            if (!$postId) {
                $oldUrls[] = $url;
                continue;
            } else {
                $uuid = \get_post_meta($postId, 'oc_uuid', true);

                if ($uuid !== false && $uuid !== '') {
                    $index = array_search($url, array_keys($urls), true);
                    $uuids[$index] = $uuid;
                }
            }
        }

        if ( ! empty($oldUrls)) {
            $query = QueryBuilder::where('OriginalUrl', $oldUrls);

            $provider = OpenContentProvider::setup([
                'q' => $query->buildQueryString(),
                'contenttypes' => ['Article'],
            ]);

            $provider->setPropertyMap('Article');

            $articles = $provider->queryWithRequirements();

            if (\is_array($articles)) {
                \array_map(function ($article) use ($urls, &$uuids) {
                    $articleValue = \array_search($article->originalUrl, $urls, true);

                    if (\is_int($articleValue)) {
                        $uuids[$articleValue] = $article->uuid;
                    }
                }, $articles);

                \ksort($uuids);
                $uuids = \array_values($uuids);
            }
        }

        return $uuids;
    }

    /**
     * Get articles from specific positions
     *
     * @param int $start
     * @param int $limit
     *
     * @return array
     */
    public function getArticlesFromPosition($start = 0, $limit = 10): array
    {
        // If we have cached data we present it otherwise trigger and get data
        $articles = $this->shouldFetchData() ? $this->fetchData() : $this->getStoredData();

        if (\count($articles) === 0) {
            $articles = $this->getBackupData();
        }

        $start = empty($start) ? 0 : $start;

        return \array_slice($articles, \max($start - 1, 0), $limit);
    }

    /**
     * Fetch the data from Google Analytics.
     *
     * @param BetaAnalyticsDataClient $client
     * 
     * @return RepeatedField|null
     */
    private function getAnalytics(BetaAnalyticsDataClient $client): ?RepeatedField
    {
        $options = [
            'property' => 'properties/' . $this->getPropertyId(),
            'dateRanges' => $this->getDateRange(),
            'dimensions' => [
                new Dimension(['name' => 'pagePath'])
            ],
            'metrics' => [
                new Metric(['name' => 'screenPageViews'])
            ],
            'orderBys' => [
                new OrderBy([
                    'desc' => true,
                    'metric' => new MetricOrderBy(['metric_name' => 'screenPageViews'])
                ])
            ],
            'dimensionFilter' => new FilterExpression([
                'filter' => new Filter([
                    'field_name' => 'pagePath',
                    'string_filter' => new StringFilter([
                        'match_type' => $this->customSettings['match_type'] ?? MatchType::PARTIAL_REGEXP,
                        'value' => $this->customSettings['value'] ?? '\/(?:[0-9a-zA-Z-])*\/.+',
                        'case_sensitive' => $this->customSettings['case_sensitive'] ?? false
                    ])
                ])
            ]),
            'limit' => static::$limit,
        ];

        try {
            $response = $client->runReport($options);

            return $response->getRows();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Fetch Credentials added in admin
     *
     * @return Credentials
     */
    public function getCredentials(): Credentials
    {
        return $this->settings->getCredentials();
    }

    /**
     * Fetch the date range to fetch the data from from admin settings
     *
     * @return array
     */
    public function getDateRange(): array
    {
        return [
            new DateRange($this->settings->getDateRange())
        ];
    }

    /**
     * Check if property has been set in settings
     *
     * @return bool
     */
    public function hasPropertyId(): bool
    {
        return $this->getPropertyId() && Str::notEmpty($this->getPropertyId());
    }

    /**
     * @param array $options
     *
     * @return void
     */
    public function customize(array $options = []): void
    {
        $this->customSettings = $options;
        $this->cacheKey = null;
    }

    public function getMatchTypes()
    {
        return array_map(function($type) {
            return [
                'value' => $type, 
                'text' => MatchType::name($type)
            ];
        }, $this->matchTypes);
    }

    /**
     * Determine if a data is stored
     *
     * @return bool
     */
    private function shouldFetchData(): bool
    {
        return Cache::get($this->getCacheKey()) === false;
    }

    /**
     * Get cache key
     * 
     * @return string
     */
    private function getCacheKey(): string
    {
        if ($this->cacheKey) {
            return $this->cacheKey;
        }

        $this->cacheKey = self::GA_CACHE_KEY . '_' . $this->getPropertyId();

        if ($this->customSettings) {
            $checksum = (string)crc32(serialize($this->customSettings));
            $hash = base_convert($checksum, 10, 36);

            $this->cacheKey = $this->cacheKey . '_' . $hash;
        }
        
        return $this->cacheKey;
    }

    /**
     * Store backup of data
     *
     * @param array $data
     *
     * @return bool
     */
    private function storeBackup(array $data = []): bool
    {
        return Cache::set($this->getCacheKey() . '_extended', $data, 2 * \WEEK_IN_SECONDS);
    }

    /**
     * Store data in the cache.
     *
     * @param $data
     *
     * @return boolean
     */
    private function storeData($data): bool
    {
        $duration = $data ? static::GA_CACHE_INTERVAL : 1;

        Cache::set($this->getCacheKey(), $data, 60 * $duration);

        if ( ! empty($data)) {
            return $this->storeBackup($data);
        }

        return false;
    }

    /**
     * Get stored data
     *
     * @return mixed
     */
    private function getStoredData()
    {
        if (($cached = Cache::get($this->getCacheKey())) !== false) {
            return $cached;
        }

        return [];
    }

    /**
     * @return array|mixed
     */
    private function getBackupData()
    {
        if (($cachedBackup = Cache::get($this->getCacheKey() . '_extended')) !== false) {
            NewRelicLog::error('Serves Google Analytics backup data');

            return $cachedBackup;
        }

        return [];
    }

    /**
     * Get property ID
     *
     * @return mixed
     */
    private function getPropertyId()
    {
        return $this->settings->getPropertyId();
    }

    /**
     * @param Credentials $credentials
     *
     * @return void
     */
    private function invalidateCredentials(Credentials $credentials): void
    {
        if ( ! empty($credentials->project_id)) {
            Cache::set(static::GA_VALID_CREDENTIALS, static::NOT_VALID_CREDENTIALS, 60 * static::GA_CACHE_INTERVAL);
        }
    }

    /**
     * @param Credentials $credentials
     *
     * @return bool
     */
    private function isValidCredentials(Credentials $credentials): bool
    {
        if (empty($credentials->project_id)) {
            return false;
        }

        return Cache::get(static::GA_VALID_CREDENTIALS, '') !== static::NOT_VALID_CREDENTIALS;
    }
}
