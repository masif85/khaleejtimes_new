<?php

namespace Everyware\Everyboard;

use Everyware\Everyboard\Exceptions\InvalidQueryOrUnauthorized;
use Everyware\Everyboard\Exceptions\InvalidSorting;
use Everyware\Helpers\OcProperties;
use Everyware\Storage\Traits\MemoryStorage;
use OcArticle;

class OcArticleProvider
{
    use MemoryStorage;

    /**
     * @var OcApiAdapter
     */
    private $ocApi;

    /**
     * @var OcProperties
     */
    private $properties;

    /**
     * @var array
     */
    private $queryInfo;

    public function __construct(OcApiAdapter $ocApi, OcProperties $properties)
    {
        $this->ocApi = $ocApi;
        $this->properties = $properties;
    }

    public function getArticle($uuid, $use_cache = true): ?OcArticle
    {
        if ($this->inMemory($uuid)) {
            return $this->getFromMemory($uuid);
        }

        $object = $this->ocApi->get_single_object($uuid, $this->properties->all(), '', $use_cache);

        $article = $object instanceof OcArticle ? $object : null;

        // Store result regardless so we dont try to fetch multiple times if article is null
        $this->addToMemory($uuid, $article);

        return $article;
    }

    /**
     * @param string      $query
     * @param int         $limit
     * @param int         $start
     * @param string|null $sorting
     *
     * @return array
     */
    public function getArticlesByQuery(string $query, int $limit, int $start = 0, string $sorting = null): array
    {
        try {
            return $this->search(array_replace([
                'q' => $query,
                'contenttype' => 'Article',
                'start' => $start,
                'limit' => $limit
            ], $this->getSortParams($sorting)));
        } catch (InvalidSorting $e) {
            error_log($e->getMessage());
        }

        return [];

    }

    public function getHitsFromLastQuery(): int
    {
        return $this->queryInfo['hits'] ?? 0;
    }

    public function getDurationFromLastQuery(): int
    {
        return $this->queryInfo['duration'] ?? 0;
    }

    public function search(array $params, bool $useCache = true): array
    {
        try {
            return array_map([$this, 'getArticle'], $this->searchUuids($params, $useCache));
        } catch (InvalidQueryOrUnauthorized $e) {
            error_log($e->getMessage());
        }

        return [];
    }

    /**
     * @param string $query
     * @param int    $limit
     *
     * @return int
     * @throws InvalidQueryOrUnauthorized
     */
    public function testQuery(string $query = '', int $limit = 0): int
    {
        $params = [
            'limit' => $limit
        ];

        if ( ! empty($query)) {
            $params['q'] = $query;
        }

        $this->searchUuids($params, false);

        return $this->getHitsFromLastQuery();
    }

    /**
     * @param array $params
     * @param bool  $useCache
     *
     * @return array
     * @throws InvalidQueryOrUnauthorized
     */
    public function searchUuids(array $params = [], $useCache = true): array
    {
        $params['contenttype'] = 'Article';
        $params['properties'] = ['uuid', 'contenttype'];

        $query = $params['q'] ?? '';

        $result = $this->ocApi->search($params, $useCache, false);

        if (empty($result)) {
            throw new InvalidQueryOrUnauthorized(
                sprintf(__('Invalid query "%s", or unauthorized.', 'everyboard'), $query)
            );
        }

        return $this->filteredResult($result);
    }

    private function filteredResult(array $result = []): array
    {
        if ( ! empty($result)) {

            $this->setQueryInfo($result['hits'], $result['duration']);

            unset($result['facet'], $result['hits'], $result['duration']);
        }
        $uuids = array_column($result, 'uuid');

        return array_merge(...$uuids);
    }

    private function setQueryInfo(int $hits, int $duration): void
    {
        $this->queryInfo = [
            'hits' => $hits,
            'duration' => $duration
        ];
    }

    /**
     * @param string $sortingName
     *
     * @return object[] [ { string indexfield, bool ascending }, ... ]
     */
    private function getSortIndexFields(string $sortingName): array
    {
        $sort_options = $this->ocApi->get_oc_sort_options();
        if (isset($sort_options->sortings)) {
            foreach ($sort_options->sortings as $sorting) {
                if ($sorting->name === $sortingName) {
                    return $sorting->sortIndexFields;
                }
            }
        }

        return [];
    }

    private function getSortParams(string $sortingName): array
    {
        if (empty($sortingName)) {
            return [];
        }

        $sort = $this->getSortIndexFields($sortingName);
        if (empty($sort)) {
            throw new InvalidSorting(sprintf(__('Invalid sorting "%s"', 'everyboard'), $sortingName));
        }

        return [
            'sort.indexfield' => $sort[0]->indexField,
            "sort.{$sort[0]->indexField}.ascending" => ($sort[0]->ascending) ? 'true' : 'false'
        ];
    }

    public static function create(): OcArticleProvider
    {
        $adapter = OcApiAdapter::create();

        return new static(
            $adapter,
            new OcProperties(array_unique($adapter->get_default_properties()))
        );
    }
}
