<?php declare(strict_types=1);

namespace Everyware\Everyboard;

/**
 * Class OcListAdapter
 * @package Everyware\Everyboard
 */
class OcListAdapter
{
    /**
     * @param string $uuid
     *
     * @return array
     * @throws Exceptions\ArticleRelationNotFoundException
     * @throws Exceptions\InvalidListUuid
     * @throws Exceptions\ListNotFoundException
     */
    public function get_article_uuids_or_fail(string $uuid)
    {
        return \OcList::get_article_uuids_or_fail($uuid);
    }
}
