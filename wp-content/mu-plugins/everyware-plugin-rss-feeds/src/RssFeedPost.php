<?php declare(strict_types=1);


namespace Everyware\RssFeeds;

use Infomaker\Everyware\Base\Models\Post;

/**
 * Class RssFeedPost
 * @package Everyware\RssFeeds
 */
class RssFeedPost extends Post
{
    /**
     * @var string
     */
    protected static $type = 'rss-feed';
}
