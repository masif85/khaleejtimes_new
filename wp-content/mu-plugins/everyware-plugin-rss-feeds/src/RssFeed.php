<?php declare(strict_types=1);


namespace Everyware\RssFeeds;


use Everyware\RssFeeds\Contracts\RssChannelInterface;
use Everyware\RssFeeds\Contracts\RssFeedInterface;
use Everyware\RssFeeds\Exceptions\UnexpectedRssFeedSource;
use Everyware\RssFeeds\Exceptions\UnexpectedRssLinkType;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Twig\View;
use OcArticle;

/**
 * Class RssFeed
 * @package Everyware\RssFeeds
 */
class RssFeed implements RssFeedInterface
{
    /** @var RssFeedPost */
    private $post;

    /** @var RssChannelInterface[] */
    private $channels;

    private $ocApi = null;

    private function __construct()
    {
    }

    /**
     * @param RssFeedPost $post
     *
     * @return RssFeedInterface
     */
    public static function fromRssFeedPost(RssFeedPost $post): RssFeedInterface
    {
        $result = new self();
        $result->post = $post;
        $result->channels = [];

        return $result;
    }

    /**
     * @return array
     */
    public function getItemSettings(): array
    {
        return $this->post->getMeta('rss-item-settings', []);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->post->post_title ?? '';
    }

    /**
     * @return string
     * @throws UnexpectedRssLinkType
     */
    public function getLink(): string
    {
        $feedSettings = $this->getFeedSettings();

        switch ($feedSettings['link_type']) {
            case 'page':
                return Page::createFromId($feedSettings['link_page'])->permalink();
            case 'url':
                return $feedSettings['link_url'];
            default:
                throw new UnexpectedRssLinkType('Unexpected RSS link type `' . $feedSettings['link_type'] . '`.');
        }
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->getFeedSettings()['description'] ?? '';
    }

    /**
     * @return array
     */
    public function getFeedSettings(): array
    {
        return $this->post->getMeta('rss-feed-settings', []);
    }

    /**
     * Add a channel to the feed.
     *
     * @param RssChannelInterface $channel
     */
    public function addChannel(RssChannelInterface $channel): void
    {
        $this->channels[] = $channel;
    }

    /**
     * Load Open Content articles to present in this feed.
     *
     * @return OcArticle[]
     * @throws UnexpectedRssFeedSource
     */
    public function getOcArticles(): array
    {
        $feedSettings = $this->getFeedSettings();

        switch ($feedSettings['feed_source']) {
            case 'list':
                return $this->getOcApiHandler()->getArticlesByList($feedSettings['feed_source_list']);
            case 'query':
                return $this->getOcApiHandler()->getArticlesByQuery(
                    $feedSettings['feed_source_query'],
                    $feedSettings['feed_source_query_sorting'],
                    $feedSettings['feed_source_query_start'],
                    $feedSettings['feed_source_query_limit']
                );
            default:
                throw new UnexpectedRssFeedSource('Unexpected RSS feed source `' . $feedSettings['feed_source'] . '`.');
        }
    }

    /**
     * Export feed as XML.
     *
     * @return string
     */
    public function toXml(): string
    {
        $channels = array_map(static function(RssChannelInterface $array){
            return $array->getViewItems();
        }, $this->channels);

        return View::generate('@rssFeedsPlugin/views/rss-feed.twig', [
            'channels' => $channels
        ]);
    }

    /**
     * Override the default OC API.
     *
     * @param mixed $ocApi
     */
    public function setDefaultOcApi($ocApi)
    {
        $this->ocApi = $ocApi;
    }

    /**
     * Initialize and return OcApiHandler.
     *
     * @return OcApiHandler
     */
    private function getOcApiHandler(): OcApiHandler
    {
        $handler = new OcApiHandler();

        if ($this->ocApi !== null) {
            $handler->setDefaultApi($this->ocApi);
        }

        return $handler;
    }
}
