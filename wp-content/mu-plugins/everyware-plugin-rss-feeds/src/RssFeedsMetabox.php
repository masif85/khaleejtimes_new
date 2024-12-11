<?php declare(strict_types=1);


namespace Everyware\RssFeeds;


/**
 * Class RssFeedsMetabox
 *
 * Updates Metabox functionality and sets some properties common to the RssFeeds metaboxes.
 *
 * @package Everyware\RssFeeds
 */
abstract class RssFeedsMetabox extends Metabox
{
    protected static $boxPosition = 'normal';

    protected static $boxPriority = 'high';

    protected $textDomain = RSS_FEEDS_TEXT_DOMAIN;

    protected $templatePath = '@rssFeedsPlugin/parts/';

    protected $postType = RssFeeds::POST_TYPE_ID;

    protected $postClass = RssFeedPost::class;
}
