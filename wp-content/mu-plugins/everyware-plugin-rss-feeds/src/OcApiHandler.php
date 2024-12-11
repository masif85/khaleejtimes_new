<?php declare(strict_types=1);


namespace Everyware\RssFeeds;


use Everyware\RssFeeds\Exceptions\InvalidListUuid;
use Everyware\RssFeeds\Exceptions\InvalidQueryOrUnauthorized;
use Everyware\RssFeeds\Exceptions\InvalidSorting;
use OcAPI;
use OcArticle;
use OcObject;
use Unit\Everyware\RssFeeds\FakeOcArticle;

/**
 * Class OcApiHandler
 * @package Everyware\RssFeeds
 */
class OcApiHandler
{
    /**
     * @return array
     */
    public function getLists(): array
    {
        $options = json_decode(get_option(self::$OCLIST_OPTIONS_NAME), true);
        $result = $this->getDefaultApi()->search([
            'q'            => $options['list_query'] ?? '*:*',
            'contenttypes' => ['List'],
            'limit'        => self::LIMIT,
            'properties'   => ['uuid', 'Name', 'Type']
        ]);

        $lists = [];

        /** @var OcObject $list */
        foreach ($result as $list) {
            if ($list instanceof OcObject && isset($list->uuid[0], $list->name[0])) {
                $lists[$list->uuid[0]] = $list->name[0];
            }
        }

        return $lists;
    }

    /**
     * @return object
     */
    public function getSortOptions(): object
    {
        return $this->getDefaultApi()->get_oc_sort_options();
    }

    /**
     * @param string $listUuid
     *
     * @return int
     * @throws InvalidListUuid
     */
    public function testArticleCountForList(string $listUuid): int
    {
        return count($this->rawGetArticlesByList($listUuid, self::MINIMUM_ARTICLE_PROPERTIES, false, false));
    }

    /**
     * @param string $query
     * @param int    $start
     * @param int    $limit
     *
     * @return int
     * @throws InvalidQueryOrUnauthorized
     */
    public function testArticleCountForQuery(string $query, int $start, int $limit): int
    {
        return count($this->rawGetArticlesByQuery($query, null, $start, $limit, self::MINIMUM_ARTICLE_PROPERTIES, false, false));
    }

    /**
     * @param string $listUuid
     *
     * @return OcArticle[]
     * @throws InvalidListUuid
     */
    public function getArticlesByList(string $listUuid): array
    {
        return $this->rawGetArticlesByList($listUuid, self::RSS_ARTICLE_PROPERTIES, self::CACHE_CAN_BE_USED, true);
    }

    /**
     * @param string $query
     * @param string $sorting
     * @param int    $start
     * @param int    $limit
     *
     * @return OcArticle[]
     * @throws InvalidQueryOrUnauthorized
     * @throws InvalidSorting
     */
    public function getArticlesByQuery(string $query, string $sorting = null, int $start, int $limit): array
    {
        return $this->rawGetArticlesByQuery($query, $sorting, $start, $limit, self::RSS_ARTICLE_PROPERTIES, self::CACHE_CAN_BE_USED, true);
    }

    /**
     * Override the default API.
     *
     * @param mixed $ocApi
     */
    public function setDefaultApi($ocApi)
    {
        $this->api = $ocApi;
    }

    /**
     * @todo replace all instances of OcArticle and object with OcArticleInterface, once it is implemented.
     *
     * @param OcArticle[] $ocArticles
     * @param string[] $uuids
     *
     * @return OcArticle[]  Sorted by UUID, in the order they come in $uuids.
     */
    private static function sortArticlesByCustomUuidOrder(array $ocArticles, array $uuids): array
    {
        $uuidsByKey = array_flip($uuids);

        usort($ocArticles, static function(object $a, object $b) use ($uuidsByKey) {
            $indexA = $uuidsByKey[$a->get_value('uuid')];
            $indexB = $uuidsByKey[$b->get_value('uuid')];
            if ($indexA === $indexB) {
                return 0;
            }
            return ($indexA < $indexB) ? -1 : 1;
        });

        return $ocArticles;
    }

    /**
     * @param string $listUuid
     * @param array  $properties
     * @param bool   $useCache
     * @param bool   $createArticle
     *
     * @return OcArticle[]
     * @throws InvalidListUuid
     */
    private function rawGetArticlesByList(string $listUuid, array $properties, bool $useCache = false, bool $createArticle = false): array
    {
        /** @var OcObject $result */
        $result = $this->getDefaultApi()->get_single_object($listUuid);
        if (!(isset($result) && $result instanceof OcObject)) {
            throw new InvalidListUuid('Invalid list UUID `' . $listUuid . '`.');
        }

        // Load all UUIDs mentioned, making sure they all have contenttype 'Article'.
        $articleUuids = $result->get('articleuuids');
        $params = [
            'q'            => 'uuid:(' . implode(' OR ', $articleUuids) . ')',
            'contenttypes' => ['Article'],
            'limit'        => count($articleUuids),
            'properties'   => $properties
        ];
        $articles = $this->getDefaultApi()->search($params, $useCache, $createArticle);

        $articles = array_filter($articles, static function ($item) {
            // todo replace OcArticle and FakeOcArticle with OcArticleInterface, once they implement it.
            return ($item instanceof OcArticle || $item instanceof FakeOcArticle);
        });

        return self::sortArticlesByCustomUuidOrder($articles, $articleUuids);
    }

    /**
     * @param string      $query
     * @param string|null $sorting
     * @param int         $start
     * @param int         $limit
     * @param array       $properties
     * @param bool        $useCache
     * @param bool        $createArticle
     *
     * @return OcArticle[]
     * @throws InvalidQueryOrUnauthorized
     * @throws InvalidSorting
     */
    private function rawGetArticlesByQuery(string $query, string $sorting = null, int $start = 0, int $limit = self::LIMIT, array $properties, bool $useCache = false, bool $createArticle = false): array
    {
        $params = [
            'q'            => $query,
            'contenttypes' => ['Article'],
            'start'        => $start,
            'limit'        => $limit,
            'properties'   => $properties
        ];

        if ($sorting !== null) {
            $sort = $this->getSortIndexFields($sorting);
            if (!isset($sort[0])) {
                throw new InvalidSorting('Invalid sorting `' . $sorting . '`');
            }

            $params['sort.indexfield'] = $sort[0]->indexField;
            $params['sort.' . $sort[0]->indexField . '.ascending'] = ($sort[0]->ascending) ? 'true' : 'false';
        }

        $list = $this->getDefaultApi()->search($params, $useCache, $createArticle);
        if (count($list) === 0) {
            throw new InvalidQueryOrUnauthorized('Invalid query `' . $query . '`, or unauthorized.');
        }

        return array_filter($list, static function ($item) {
            // todo replace OcArticle and FakeOcArticle with OcArticleInterface, once they implement it.
            return ($item instanceof OcArticle || $item instanceof FakeOcArticle);
        });
    }

    /**
     * @return OcAPI|mixed
     */
    private function getDefaultApi()
    {
        if ($this->api === null) {
            $this->api = new OcAPI();
        }
        return $this->api;
    }

    /**
     * @param string $sortingName
     *
     * @return object[] [ { string indexfield, bool ascending }, ... ]
     */
    private function getSortIndexFields(string $sortingName): array
    {
        $sort_options = $this->getSortOptions();
        if (isset($sort_options->sortings)) {
            foreach ($sort_options->sortings as $sorting) {
                if ($sorting->name === $sortingName) {

                    return $sorting->sortIndexFields;
                }
            }
        }
        return [];
    }

    public static $OCLIST_OPTIONS_NAME = 'OCLIST_OPTIONS';

     /**
     * @var OcAPI|mixed
     */
    private $api;

    /**
     * @todo Change to TRUE once cache can be used reliably (without clearing out parts of the properties loaded by another process)
     */
    private const CACHE_CAN_BE_USED = false;

    /**
     * Some queries require a limit, even though you might not have one in mind.
     */
    private const LIMIT = 100;

    /**
     * The minimum properties needed to identify items as articles.
     */
    private const MINIMUM_ARTICLE_PROPERTIES = [
        'uuid',
        'contenttype'
    ];

    /**
     * All properties that RSS is going to need about articles.
     */
    private const RSS_ARTICLE_PROPERTIES = [
        'uuid',
        'contenttype',
        'TeaserRaw',
        'TeaserHeadline',
        'TeaserBody',
        'Authors.Name',
        'Pubdate',
        'Categories.Name'
    ];
}
