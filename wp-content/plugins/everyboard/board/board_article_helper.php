<?php

use Everyware\Everyboard\OcArticleProvider;

class EveryBoard_Article_Helper
{
    protected static $oc_list_uuid_structure = [];

    /**
     * @var OcArticleProvider
     */
    private static $articleProvider;

    /**
     * Iterates over a board-json and makes ONE request to Open Content to fetch all the manual articles for a board.
     *
     * @param $board_json
     */
    public static function prefetch_board_articles($board_json = []): void
    {
        if (array_key_exists('rows', $board_json)) {
            $oc_list_structure = [];

            $article_uuids = [];

            foreach ($board_json['rows'] as $row) {

                $cols = $row['cols'] ?? [];

                foreach ($cols as $col) {

                    $content = $col['content'] ?? [];

                    foreach ($content as $content_item) {

                        // Continue if content has no type
                        if ( ! isset($content_item['type'])) {
                            continue;
                        }

                        // Set uuid and move on if type article
                        if ($content_item['type'] === 'oc_article') {
                            $article_uuids[] = $content_item['oc_uuid'];
                            continue;
                        }

                        // Construct data with the OC Lists and positions that are being used in the board.
                        if ($content_item['type'] === 'oclist_widget') {

                            $list_uuid = isset($content_item['settings']['list']) ? $content_item['settings']['list'] : '0';

                            if ($list_uuid === '0' || ! isset($content_item['settings']['listposition'], $content_item['settings']['listlimit'])) {
                                continue;
                            }

                            // Preset list from list uuid if it don't already exists
                            if ( ! isset($oc_list_structure[$list_uuid])) {
                                $oc_list_structure[$list_uuid] = [];
                            }

                            $list_start = (int)$content_item['settings']['listposition'] - 1;
                            $list_start = $list_start < 0 ? 0 : $list_start;

                            $list_limit = (int)$content_item['settings']['listlimit'];
                            $list_stop = $list_start + $list_limit;

                            // Iterate through selected positions and add article_uuids
                            for ($i = $list_start; $i < $list_stop; $i++) {

                                if ( ! in_array($i, $oc_list_structure[$list_uuid], true)) {
                                    $oc_list_structure[$list_uuid][] = $i;
                                }
                            }
                        }
                    }
                }
            }

            // Fetch article uuids from OC List to be able to include these articles in the batch query.
            foreach ($oc_list_structure as $list_id => $positions) {

                $articles = OcList::get_approved_list_articles($list_id);

                $oc_list_articles = [];
                foreach ($positions as $position) {
                    $article_uuid = isset($articles[$position], $articles[$position]->uuid) ? $articles[$position]->uuid[0] : null;
                    $oc_list_articles[$position] = $article_uuid;

                    // Add uuid to query
                    if ($article_uuid !== null) {
                        $article_uuids[] = $article_uuid;
                    }
                }

                static::$oc_list_uuid_structure[$list_id] = $oc_list_articles;
            }

            static::prefetch_articles($article_uuids);
        }
    }

    /**
     * Function that prefetches articles from OC by an array of uuids.
     * If it's able to fetch it returns true, otherwise null
     *
     * @param $uuids
     *
     * @return bool|null
     */
    public static function prefetch_board_list_articles($uuids = []): ?bool
    {
        if ( ! static::prefetch_articles($uuids)) {
            return null;
        }

        return true;
    }

    public static function get_oc_article($uuid, $force_fetch = false): ?OcArticle
    {
        return static::ocArticleProvider()->getArticle($uuid, $force_fetch !== true);
    }

    /**
     * Helps out by placing prefetched articles in correct order and place from a oc list structure.
     *
     * @param $list
     * @param $listposition
     * @param $limit
     *
     * @return array
     */
    public static function get_prefetched_oclist_articles($list, $listposition, $limit = 1): array
    {
        if ( ! isset(static::$oc_list_uuid_structure[$list])) {
            return [];
        }

        $current_list = static::$oc_list_uuid_structure[$list];

        $articles = [];
        for ($i = $listposition; $i < $listposition + $limit; $i++) {
            $articles[] = isset($current_list[$i]) ? static::get_oc_article($current_list[$i]) : null;
        }

        return $articles;
    }

    /**
     * Trying to fetch the article from OC/Cache instead of returning it from the prefetched array
     *
     * @param $uuid
     *
     * @return OcArticle|null
     */
    public static function force_fetch_oc_article($uuid): ?OcArticle
    {
        return static::ocArticleProvider()->getArticle($uuid, false);
    }

    /**
     * @param $uuid
     *
     * @return OcArticle|null
     */
    public static function force_fetch_search_oc_article($uuid): ?OcArticle
    {
        return static::force_fetch_oc_article($uuid);
    }

    protected static function ocArticleProvider(): OcArticleProvider
    {
        if ( ! static::$articleProvider) {
            static::$articleProvider = OcArticleProvider::create();
        }

        return static::$articleProvider;
    }

    protected static function prefetch_articles($uuids = []): bool
    {
        $articles_fetched = false;

        foreach ($uuids as $uuid) {
            $article = static::get_oc_article($uuid);

            if ($article instanceof OcArticle) {
                $articles_fetched = true;
            }
        }

        return $articles_fetched;
    }
}
