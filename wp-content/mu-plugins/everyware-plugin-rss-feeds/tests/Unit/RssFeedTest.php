<?php declare(strict_types=1);

namespace Unit\Everyware\RssFeeds;

use Everyware\RssFeeds\Contracts\RssFeedInterface;
use Everyware\RssFeeds\Exceptions\UnexpectedRssFeedSource;
use Everyware\RssFeeds\Exceptions\UnexpectedRssLinkType;
use Everyware\RssFeeds\RssChannel;
use Everyware\RssFeeds\RssFeed;
use Infomaker\Everyware\Base\Models\Page;
use SimpleXMLElement;

/**
 * Class RssFeedTest
 * @package Unit\Everyware\RssFeeds
 */
class RssFeedTest extends RssFeedsTestCase
{
    public function testGetItemSettings(): void
    {
        self::assertSame(RssItemTest::getItemSettings1(), $this->rssFeed1->getItemSettings());

        self::assertSame(RssItemTest::getItemSettings2(), $this->rssFeed2->getItemSettings());

        self::assertSame([], $this->rssFeed3->getItemSettings());
    }

    public function testGetTitle(): void
    {
        self::assertSame('Title 1', $this->rssFeed1->getTitle());

        self::assertSame('Title 2', $this->rssFeed2->getTitle());

        self::assertSame('', $this->rssFeed3->getTitle());
    }

    public function testGetLink(): void
    {
        // Link is to a Page.
        self::assertSame('https://example.com/'.$this->page->get('id'), $this->rssFeed1->getLink());

        // Link is to a URL.
        self::assertSame('https://navigaglobal.com/', $this->rssFeed2->getLink());

        // Link type is invalid.
        $this->expectException(UnexpectedRssLinkType::class);
        $this->expectExceptionMessage('Unexpected RSS link type `angel`.');
        $this->rssFeed3->getLink();
    }

    public function testGetDescription(): void
    {
        self::assertSame('Description 1', $this->rssFeed1->getDescription());

        self::assertSame('Description 2', $this->rssFeed2->getDescription());

        // Description is missing.
        self::assertSame('', $this->rssFeed3->getDescription());
    }

    public function testGetFeedSettings(): void
    {
        self::assertSame($this->feedSettings1, $this->rssFeed1->getFeedSettings());

        self::assertSame($this->feedSettings2, $this->rssFeed2->getFeedSettings());

        self::assertSame($this->feedSettings3, $this->rssFeed3->getFeedSettings());
    }

    public function testAddChannel(): void
    {
        self::assertSame(0, self::countChannelsInFeed($this->rssFeed1));

        $channel1 = new RssChannel('Channel title 1', 'https://channel-link1.org/', 'Channel description 1');
        $this->rssFeed1->addChannel($channel1);

        self::assertSame(1, self::countChannelsInFeed($this->rssFeed1));

        $channel2 = new RssChannel('Channel title 2', 'https://channel-link2.org/', 'Channel description 2');
        $this->rssFeed1->addChannel($channel2);

        self::assertSame(2, self::countChannelsInFeed($this->rssFeed1));
    }

    public function testGetOcArticles(): void
    {
        $articles1 = $this->rssFeed1->getOcArticles();
        self::assertCount(2, $articles1);

        $articles2 = $this->rssFeed2->getOcArticles();
        self::assertCount(3, $articles2);
    }

    public function testGetOcArticlesWithUnexpectedFeedSource(): void
    {
        $this->expectException(UnexpectedRssFeedSource::class);
        $this->rssFeed3->getOcArticles();
    }

    public function testToXml(): void
    {
        self::assertSame('<?xml version="1.0" encoding="UTF-8" ?><rss version="2.0"></rss>', $this->rssFeed1->toXml());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->page = get_post(135);

        // Showcases linking to a Page, and getting articles from a List.
        $this->feedSettings1 = [
            'description'               => 'Description 1',
            'link_type'                 => 'page',
            'link_page'                 => $this->page->get('id'),
            'link_url'                  => null,
            'feed_source'               => 'list',
            'feed_source_list'          => 'ghi-789',
            'feed_source_query'         => null,
            'feed_source_query_sorting' => null,
            'feed_source_query_start'   => null,
            'feed_source_query_limit'   => null,
            'current_version'           => '1.0.0'
        ];
        $post1 = new FakeRssFeedPost();
        $post1->set('post_title', 'Title 1');
        $post1->addMeta('rss-feed-settings', $this->feedSettings1);
        $post1->addMeta('rss-item-settings', RssItemTest::getItemSettings1());

        // Showcases linking to a URL, and getting articles from a Query.
        $this->feedSettings2 = [
            'description'               => 'Description 2',
            'link_type'                 => 'url',
            'link_page'                 => null,
            'link_url'                  => 'https://navigaglobal.com/',
            'feed_source'               => 'query',
            'feed_source_list'          => null,
            'feed_source_query'         => '*:*',
            'feed_source_query_sorting' => FakeOcAPI::SORT_INDEX_NAME1,
            'feed_source_query_start'   => 1,
            'feed_source_query_limit'   => 10,
            'current_version'           => '1.0.0'
        ];
        $post2 = new FakeRssFeedPost();
        $post2->set('post_title', 'Title 2');
        $post2->addMeta('rss-feed-settings', $this->feedSettings2);
        $post2->addMeta('rss-item-settings', RssItemTest::getItemSettings2());

        // Showcases invalid and/or missing values, that should not be possible to set from the admin.
        $this->feedSettings3 = [
            'link_type'                 => 'angel',
            'feed_source'               => 'devil'
        ];
        $post3 = new FakeRssFeedPost();
        $post3->addMeta('rss-feed-settings', $this->feedSettings3);

        $this->rssFeed1 = RssFeed::fromRssFeedPost($post1);
        $this->rssFeed1->setDefaultOcApi(new FakeOcAPI());

        $this->rssFeed2 = RssFeed::fromRssFeedPost($post2);
        $this->rssFeed2->setDefaultOcApi(new FakeOcAPI());

        $this->rssFeed3 = RssFeed::fromRssFeedPost($post3);
        $this->rssFeed3->setDefaultOcApi(new FakeOcAPI());
    }

    private static function countChannelsInFeed(RssFeedInterface $feed): int
    {
        $simpleXml = new SimpleXMLElement($feed->toXml());

        return count($simpleXml->channel);
    }

    /**
     * @var Page
     */
    private $page;

    /**
     * @var array
     */
    private $feedSettings1;

    /**
     * @var array
     */
    private $feedSettings2;

    /**
     * @var array
     */
    private $feedSettings3;

    /**
     * @var RssFeedInterface
     */
    private $rssFeed1;

    /**
     * @var RssFeedInterface
     */
    private $rssFeed2;

    /**
     * @var RssFeedInterface
     */
    private $rssFeed3;
}

