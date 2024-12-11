<?php declare(strict_types=1);

namespace Unit\Everyware\Plugin\RedirectOriginalUrls;

use Exception;
use OcAPI;
use OcObject;

/**
 * Class FakeOcAPI
 *
 * Imitates parts of {@see OcAPI}, giving some fake responses.
 *
 * @package Unit\Everyware\Plugin\RedirectOriginalUrls
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
        ];
    }

    /**
     * @param array    $params
     * @param bool     $use_cache
     * @param bool     $create_article
     * @param int|null $cache_ttl
     *
     * @return array|string
     */
    public function search(array $params = [], $use_cache = true, $create_article = true, $cache_ttl = null)
    {
        $result = [];

        if (!isset($params['q'])) {
            return $result;
        }

        // Handle a simple " OR "-separated list of OriginalUrls.
        $propName = 'OriginalUrls';
        if (preg_match_all('|' . $propName . ':\(("[^"]+"( OR "[^"]+")+)\)|', $params['q'], $matches)) {
            $searchUrls = explode('" OR "', trim($matches[1][0], '"'));

            foreach ($this->articles as $article) {
                $articleUrls = $article->get($propName);
                foreach ($articleUrls as $url) {
                    if (in_array($url, $searchUrls, true)) {
                        $result[] = $article;
                        break;
                    }
                }
            }
        }

        $result['hits'] = count($result);

        return $result;
    }

    /**
     * @param string $uuid
     *
     * @return OcObject
     */
    public function get_single_object(string $uuid): OcObject
    {
        return $this->getArticleByUuid($uuid);
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

    /**
     * @var FakeOcArticle[]
     */
    private $articles;
}
