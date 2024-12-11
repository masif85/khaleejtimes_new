<?php declare(strict_types=1);


namespace Unit\Everyware\RssFeeds;


use Everyware\RssFeeds\RssFeedPost;

/**
 * Class FakeRssFeedPost
 *
 * Can set/get metadata without storing it in Wordpress.
 *
 * @package Unit\Everyware\RssFeeds
 */
class FakeRssFeedPost extends RssFeedPost
{
    public function __construct()
    {
        $this->meta = [];
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return bool
     */
    public function addMeta(string $key, $value)
    {
        $this->meta[$key] = $value;

        return true;
    }

    /**
     * @param string $key
     * @param array  $default
     *
     * @return mixed
     */
    public function getMeta(string $key = '', $default = [])
    {
        return (isset($this->meta[$key])) ? $this->meta[$key] : $default;
    }

    /**
     * @var array
     */
    private $meta;
}
