<?php declare(strict_types=1);


namespace Everyware\Everyboard;


use Everyware\Everyboard\Exceptions\InvalidListUuid;
use Everyware\Everyboard\Exceptions\InvalidQueryOrUnauthorized;
use Everyware\Everyboard\Exceptions\InvalidSorting;
use OcAPI;
use OcArticle;
use OcObject;

/**
 * Class OcApiHandler
 * @package Everyware\Everyboard
 * @todo    Refactor this to Everyware package!
 */
class OcApiHandler
{
    public const TEXT_DOMAIN = 'everyboard';

    /**
     * Ajax function to validate the number of articles in an OC list.
     *
     * @param OcApiHandler|mixed $ocApiHandler
     *
     * @return OcApiResponse
     * @global string            $_REQUEST ['uuid']
     *
     */
    public static function validateOcList($ocApiHandler): OcApiResponse
    {
        $request = stripslashes_deep($_REQUEST);

        try {
            $count = $ocApiHandler->testArticleCountForList($request['uuid']);
        } catch (InvalidListUuid $e) {
            return new OcApiResponse(
                OcApiResponse::RESPONSE_TYPE_ERROR,
                __('Invalid UUID', self::TEXT_DOMAIN)
            );
        }

        if ($count === 0) {
            return new OcApiResponse(
                OcApiResponse::RESPONSE_TYPE_WARNING,
                __('No articles in list', self::TEXT_DOMAIN)
            );
        }

        return new OcApiResponse(
            OcApiResponse::RESPONSE_TYPE_OK,
            str_replace('%n', $count, __('%n articles', self::TEXT_DOMAIN))
        );
    }

    /**
     * Ajax function to validate the number of articles from an OC query.
     *
     * @param OcApiHandler|mixed $ocApiHandler
     *
     * @return OcApiResponse
     * @global string            $_REQUEST ['limit']
     *
     * @global string            $_REQUEST ['query']
     * @global string            $_REQUEST ['start']
     */
    public static function validateOcQuery($ocApiHandler): OcApiResponse
    {
        $request = stripslashes_deep($_REQUEST);

        try {
            $count = $ocApiHandler->testArticleCountForQuery($request['query'], (int)$request['start'],
                (int)$request['limit']);
        } catch (InvalidQueryOrUnauthorized $e) {
            return new OcApiResponse(
                OcApiResponse::RESPONSE_TYPE_ERROR,
                __('Invalid query, or unauthorized', self::TEXT_DOMAIN)
            );
        }

        if ($count === 0) {
            return new OcApiResponse(
                OcApiResponse::RESPONSE_TYPE_WARNING,
                __('No articles found', self::TEXT_DOMAIN)
            );
        }

        return new OcApiResponse(
            OcApiResponse::RESPONSE_TYPE_OK,
            str_replace('%n', $count, __('%n articles', self::TEXT_DOMAIN))
        );
    }

    /**
     * @return array
     */
    public function getLists(): array
    {
        $options = json_decode(get_option(self::$OCLIST_OPTIONS_NAME), true);
        $result = $this->getDefaultApi()->search([
            'q' => $options['list_query'] ?? '*:*',
            'contenttypes' => ['List'],
            'limit' => self::LIMIT,
            'properties' => ['uuid', 'Name', 'Type']
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
        return count($this->rawGetArticlesByQuery($query, null, $start, $limit, self::MINIMUM_ARTICLE_PROPERTIES, false,
            false));
    }

    /**
     * @param string $listUuid
     *
     * @return OcArticle[]
     * @throws InvalidListUuid
     */
    public function getArticlesByList(string $listUuid): array
    {
        return $this->rawGetArticlesByList($listUuid, [], true, true);
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
        return $this->rawGetArticlesByQuery($query, $sorting, $start, $limit, [], true, true);
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
     * @param OcArticle[] $ocArticles
     * @param string[]    $uuids
     *
     * @return OcArticle[]  Sorted by UUID, in the order they come in $uuids.
     * @todo replace all instances of OcArticle and object with OcArticleInterface, once it is implemented.
     *
     */
    private static function sortArticlesByCustomUuidOrder(array $ocArticles, array $uuids): array
    {
        $uuidsByKey = array_flip($uuids);

        usort($ocArticles, static function (object $a, object $b) use ($uuidsByKey) {
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
     * @param string     $listUuid
     * @param array|null $properties
     * @param bool       $useCache
     * @param bool       $createArticle
     *
     * @return OcArticle[]
     * @throws InvalidListUuid
     */
    private function rawGetArticlesByList(
        string $listUuid,
        array $properties = null,
        bool $useCache = false,
        bool $createArticle = false
    ): array {
        /** @var OcObject $result */
        $result = $this->getDefaultApi()->get_single_object($listUuid);
        if ( ! (isset($result) && $result instanceof OcObject)) {
            throw new InvalidListUuid('Invalid list UUID `' . $listUuid . '`.');
        }

        // Load all UUIDs mentioned, making sure they all have contenttype 'Article'.
        $articleUuids = $result->get('articleuuids');
        $params = [
            'q' => 'uuid:(' . implode(' OR ', $articleUuids) . ')',
            'contenttypes' => ['Article'],
            'limit' => count($articleUuids)
        ];
        if (count($properties) !== null) {
            $params['properties'] = $properties;
        }
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
     * @param array|null  $properties
     * @param bool        $useCache
     * @param bool        $createArticle
     *
     * @return OcArticle[]
     * @throws InvalidQueryOrUnauthorized
     * @throws InvalidSorting
     */
    private function rawGetArticlesByQuery(
        string $query,
        string $sorting = null,
        int $start = 0,
        int $limit = self::LIMIT,
        array $properties = null,
        bool $useCache = false,
        bool $createArticle = false
    ): array {
        $params = [
            'q' => $query,
            'contenttypes' => ['Article'],
            'start' => $start,
            'limit' => $limit
        ];

        if (count($properties) !== null) {
            $params['properties'] = $properties;
        }

        if ($sorting !== null) {
            $sort = $this->getSortIndexFields($sorting);
            if ( ! isset($sort[0])) {
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
}
