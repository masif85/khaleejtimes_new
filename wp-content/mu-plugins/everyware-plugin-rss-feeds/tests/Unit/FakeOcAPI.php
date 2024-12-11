<?php declare(strict_types=1);

namespace Unit\Everyware\RssFeeds;

use Everyware\RssFeeds\Exceptions\InvalidListUuid;
use Exception;
use OcAPI;
use OcObject;

/**
 * Class FakeOcAPI
 *
 * Imitates parts of {@see OcAPI}, giving some fake responses.
 *
 * @package Unit\Everyware\RssFeeds
 */
class FakeOcAPI
{
    public function __construct()
    {
        $this->articles = [
            new FakeOcArticle([
                'uuid' => 'aaa'
            ]),
            new FakeOcArticle([
                'uuid' => 'bbb'
            ]),
            new FakeOcArticle([
                'uuid' => 'ccc'
            ]),
            new FakeOcArticle([
                'uuid' => 'ddd'
            ]),
        ];


        $this->lists = [
            new OcObject(),
            new OcObject(),
            new OcObject(),
            new OcObject()
        ];

        $this->lists[0]->set_properties([
            'uuid'         => ['abc-123'],
            'name'         => ['All articles'],
            'articleUuids' => ['aaa', 'bbb', 'ccc', 'ddd']
        ]);
        $this->lists[1]->set_properties([
            'uuid'         => ['def-456'],
            'name'         => ['All articles, reverse order'],
            'articleUuids' => ['ddd', 'ccc', 'bbb', 'aaa']
        ]);
        $this->lists[2]->set_properties([
            'uuid'         => ['ghi-789'],
            'name'         => ['Middle articles'],
            'articleUuids' => ['bbb', 'ccc']
        ]);
        $this->lists[3]->set_properties([
            'uuid'         => ['jkl-012'],
            'name'         => ['No articles'],
            'articleUuids' => []
        ]);
    }

    /**
     * @param array $params
     * @param bool  $use_cache
     * @param bool  $create_article
     * @param int   $cache_ttl
     *
     * @return array|string
     */
    public function search(array $params = [], $use_cache = true, $create_article = true, $cache_ttl = null)
    {
        // Emulate search from OcApiHandler::getLists().
        if (isset($params['contenttypes']) && $params['contenttypes'] === ['List']) {
            return $this->lists;
        }

        // Emulate search from OcApiHandler::rawGetArticlesByList().
        if (isset($params['contenttypes']) && $params['contenttypes'] === ['Article'] &&
            isset($params['q']) && preg_match('/uuid:\(([^ ]+( OR )?)+\)/', $params['q'])) {

            $result = [];

            preg_match_all('/uuid:\(([^\)]+)\)/', $params['q'], $matches);
            $articleUuids = explode(' OR ', $matches[1][0]);
            foreach ($articleUuids as $articleUuid) {
                $result[] = $this->getArticleByUuid($articleUuid);
            }

            $result['hits'] = count($result);

            return $result;
        }

        // Emulate search from OcApiHandler::rawGetArticlesByQuery().
        if (isset($params['contenttypes']) && $params['contenttypes'] === ['Article'] &&
            isset($params['q']) && isset($params['start']) && isset($params['limit'])) {

            $result = [];

            // This is the only supported query.
            $queryIsValid = ($params['q'] === '*:*');

            if ($queryIsValid) {
                $result = $this->articles;
            }

            // Let's say that this sorting will result in the opposite order.
            if (isset($params['sort.indexfield']) && $params['sort.indexfield'] === self::SORT_INDEX_KEY1) {
                $result = array_reverse($result);
            }

            $result = array_slice($result, $params['start'], $params['limit']);

            if ($queryIsValid) {
                $result['hits'] = count($result);
            }

            return $result;
        }

        return [];
    }

    /**
     * Function to get OC Sort options
     *
     * @return object
     */
    public function get_oc_sort_options(): object
    {
        return json_decode('{
            "sortings": [
                {
                    "name": "' . self::SORT_INDEX_NAME1 . '",
                    "contentType": null,
                    "sortIndexFields": [
                        {
                            "indexField": "' . self::SORT_INDEX_KEY1 . '",
                            "ascending": false
                        }
                    ]
                }
            ]
        }');
    }

    /**
     * @param string $listUuid
     *
     * @return int
     * @throws InvalidListUuid
     */
    public function testArticleCountForList(string $listUuid): int
    {
        return count($this->getArticlesByList($listUuid));
    }

    /**
     * @param string $listUuid
     *
     * @return FakeOcArticle[]
     * @throws InvalidListUuid
     */
    public function getArticlesByList(string $listUuid): array
    {
        $result = [];

        $list = $this->getListByUuid($listUuid);
        foreach ($list->get('articleUuids') as $articleUuid) {
            $result[] = $this->getArticleByUuid($articleUuid);
        }

        return $result;
    }

    /**
     * @param string $uuid
     *
     * @return OcObject
     */
    public function get_single_object(string $uuid): OcObject
    {
        return $this->getListByUuid($uuid);
    }

    /**
     * @param string $listUuid
     *
     * @return OcObject
     * @throws InvalidListUuid
     */
    private function getListByUuid(string $listUuid): OcObject
    {
        foreach ($this->lists as $list) {
            if ($list->get('uuid')[0] === $listUuid) {
                return $list;
            }
        }
        throw new InvalidListUuid('Invalid list UUID `' . $listUuid . '`.');
    }

    /**
     * @param string $articleUuid
     *
     * @return FakeOcArticle
     * @throws Exception
     */
    private function getArticleByUuid(string $articleUuid): FakeOcArticle
    {
        foreach ($this->articles as $article) {
            if ($article->get_value('uuid') === $articleUuid) {
                return $article;
            }
        }
        throw new Exception('Invalid article UUID `' . $articleUuid . '`.');
    }

    public const SORT_INDEX_NAME1 = 'Publiceringsdag';

    public const SORT_INDEX_KEY1 = 'Pubdate';

    /**
     * @var FakeOcArticle[]
     */
    private $articles;

    /**
     * @var OcObject[]
     */
    private $lists;
}
