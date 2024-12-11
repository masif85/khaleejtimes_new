<?php
declare(strict_types=1);

namespace Unit\Everyware\RssFeeds;

use Everyware\RssFeeds\Contracts\RssChannelInterface;
use Everyware\RssFeeds\RssChannel;
use Everyware\RssFeeds\RssItem;
use Infomaker\Everyware\Twig\View;
use SimpleXMLElement;

/**
 * Class RssChannelTest
 * @package Unit\Everyware\RssFeeds
 */
class RssChannelTest extends RssFeedsTestCase
{
    /**
     * Test constructor
     */
    public function testConstruct(): void
    {
        $xmlChannel = self::asSimpleXml($this->channel);

        self::assertSame(3, count($xmlChannel->children()));
        self::assertTrue(isset($xmlChannel->title));
        self::assertTrue(isset($xmlChannel->link));
        self::assertTrue(isset($xmlChannel->description));

        self::assertSame($this->title, $xmlChannel->title->__toString());
        self::assertSame($this->link, $xmlChannel->link->__toString());
        self::assertSame($this->description, $xmlChannel->description->__toString());
    }

    /**
     * Test getViewItems
     */
    public function testGetViewItems(): void
    {
        $viewItems = $this->channel->getViewItems();

        self::assertCount(4, $viewItems);

        self::assertArrayHasKey('title', $viewItems);
        self::assertArrayHasKey('link', $viewItems);
        self::assertArrayHasKey('description', $viewItems);
        self::assertArrayHasKey('items', $viewItems);

        self::assertIsString($viewItems['title']);
        self::assertIsString($viewItems['link']);
        self::assertIsString($viewItems['description']);
        self::assertIsArray($viewItems['items']);
    }

    /**
     * Test addItem()
     */
    public function testAddItem(): void
    {
        $article1 = RssItemTest::getFakeOcArticle1();
        $item1 = RssItem::fromOcArticle($article1, RssItemTest::getItemSettings2());
        $this->channel->addItem($item1);

        $article2 = RssItemTest::getFakeOcArticle2();
        $item2 = RssItem::fromOcArticle($article2, RssItemTest::getItemSettings2());
        $this->channel->addItem($item2);

        $xmlChannel = self::asSimpleXml($this->channel);

        self::assertSame(5, count($xmlChannel->children()));
        self::assertTrue(isset($xmlChannel->item));
        self::assertSame(2, count($xmlChannel->item));

        // Assert <link> elements.
        self::assertTrue(isset($xmlChannel->item[0]->link));
        self::assertSame($article1->get_permalink(), $xmlChannel->item[0]->link->__toString());
        self::assertTrue(isset($xmlChannel->item[1]->link));
        self::assertSame($article2->get_permalink(), $xmlChannel->item[1]->link->__toString());
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->title = 'Our first RSS channel';
        $this->link = 'https://navigaglobal.com/';
        $this->description = "He's an\ninteresting fellow.";
        $this->channel = new RssChannel($this->title, $this->link, $this->description);
    }

    /**
     * Run the channel through the default template, and return as SimpleXmlElement, for easy testing.
     *
     * @param RssChannelInterface $channel
     *
     * @return SimpleXMLElement
     */
    private static function asSimpleXml(RssChannelInterface $channel): SimpleXMLElement
    {
        $xml = View::generate(self::$template, $channel->getViewItems());

        return new SimpleXMLElement($xml);
    }

    /**
     * @var string
     */
    private static $template = '@rssFeedsPlugin/views/rss-channel';

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $link;

    /**
     * @var string
     */
    private $description;

    /**
     * @var RssChannelInterface
     */
    private $channel;
}
