<?php
declare(strict_types=1);

namespace Unit\Everyware\RssFeeds;

use DateTimeImmutable;
use Everyware\RssFeeds\Contracts\RssItemInterface;
use Everyware\RssFeeds\RssItem;
use Infomaker\Everyware\Twig\View;
use Infomaker\Imengine\Imengine;
use OcObject;
use SimpleXMLElement;

/**
 * Class RssItemTest
 * @package Unit\Everyware\RssFeeds
 */
class RssItemTest extends RssFeedsTestCase
{
    /**
     * Get item settings with all things enabled.
     *
     * @return array
     */
    public static function getItemSettings1(): array
    {
        return [
            'has_title' => true,
            'has_description' => true,
            'has_dc_creator' => true,
            'has_pub_date' => true,
            'has_category' => true,
            'has_image' => true,
            'image_width' => 88,
            'has_media_credit' => true
        ];
    }

    /**
     * Get item settings with no things enabled.
     *
     * @return array
     */
    public static function getItemSettings2(): array
    {
        return [
            'has_title' => false,
            'has_description' => false,
            'has_dc_creator' => false,
            'has_pub_date' => false,
            'has_category' => false,
            'has_image' => false,
            'image_width' => null,
            'has_media_credit' => false
        ];
    }

    /**
     * Get a fake OC article (#1)
     *
     * @return FakeOcArticle
     */
    public static function getFakeOcArticle1(): FakeOcArticle
    {
        $categories = (new OcObject());
        $categories->set('Name', ['Sport']);

        $authors = (new OcObject());
        $authors->set('Name', ['Ida Bergkvist', 'Olof Carlerud']);

        return new FakeOcArticle([
            'uuid' => 'f1b03086-842d-451f-8272-decec0127f52',
            'Authors' => $authors,
            'TeaserBody' => null,
            'Pubdate' => '2020-10-13T11:02:00Z',
            'Categories' => $categories,
            'TeaserRaw' => <<<ENDTEXT
<?xml version="1.0" encoding="UTF-8"?>
<object xmlns="http://www.infomaker.se/newsml/1.0" id="MjcsMTEwLDIxMSwyMjc"
        title="Just nu: Testartikelbold headlineitalic test link test " type="x-im/teaser">
    <data>
        <title>Just nu: <strong id="strong-7041d5b6ec7d81fbd1b15150a3b17d8c">Testartikelbold</strong> headlineitalic <mark
                id="mark-cb2cde3b257d8db6d97db9c90bc379f5">test
        </mark>
            <a href="google.com" id="link-6f61ee3b72d660b86dd3bbb0dc7ce686" target="_blank" title="Google Link">link</a>
            <a download="Aftonbladet save link file" href="aftonbladet.se" id="link-1b2953d886177100b64c9b2c9c5f2cd5"
               target="_self" title="Aftonbladet title">test
            </a>
            <em id="emphasis-267260d61bb0b37c59bf6b8b3b3d6222"></em>
        </title>
        <text>
            <strong id="strong-ea95a8a77ea9c0f6989164332004984b">Testartikel</strong>
            <strong id="strong-230cd2374a3b61e23f4016b25fcfeb96">
                <em id="emphasis-839e99427678f6f2cabd4709a4b9aa07">bold and italic</em>
            </strong>
            <em id="emphasis-a46a62340803235c109ef38e54ee949a">text</em>
            <mark id="mark-552005fe10dc70ad4938a5585c7e2548">link</mark>
            <a href="https://lavender.stage.ew.ew.infomaker.io/" id="link-65cea91a4e34b8bfd2a97261aff3da5e"
               target="_blank" title="Lavender title">link
            </a>
        </text>
        <subject>Subject: Text</subject>
    </data>
    <links>
        <link rel="image" type="x-im/image" uri="im://image/dE1sFMUKHomEUxvSFYog1W8_tM8.png"
              uuid="27439dc5-97fe-5cfe-895a-72c9721eb3de">
            <data>
                <width>2556</width>
                <height>1161</height>
            </data>
            <links>
                <link rel="crop" title="16:9" type="x-im/crop"
                      uri="im://crop/0.43875/0.09366391184573003/0.35625/0.4462809917355372"/>
                <link rel="crop" title="3:2" type="x-im/crop" uri="im://crop/0.16/0/0.68125/1"/>
            </links>
        </link>
        <link rel="article" title="Intensity, pace and a sweet left foot: Arsenal's Gabriel has the full package"
              type="x-im/article" uuid="bbd66e37-40dd-4795-be17-ff7153d55643"/>
        <link rel="article"
              title="Just nu: MLB is set to implement terms of 2020 schedule after union votes down 60-game proposal"
              type="x-im/article" uuid="4abc0d0b-53d7-4d31-b272-1f11cb45b361"/>
        <link rel="article" title="Ida Five of the best: Women's Super League summer signings to watch"
              type="x-im/article" uuid="c391895b-66c7-4cac-a770-69fd503275db"/>
    </links>
</object>
ENDTEXT
        ]);
    }

    /**
     * Get a fake OC article (#2)
     *
     * @return FakeOcArticle
     */
    public static function getFakeOcArticle2(): FakeOcArticle
    {
        $categories = (new OcObject());
        $categories->set('Name', ['Världen','Rymden']);

        return new FakeOcArticle([
            'uuid' => '6de9b0bf-e3dd-4e97-89dc-416309b6cfcc',
            'Authors' => null,
            'TeaserBody' => 'S&P Idas specialtecken artikel test 1 X@X o&o <script> window.alert(\“tryggve was right\”);</script>',
            'Pubdate' => null,
            'Categories' => $categories,
            'TeaserHeadline' => 'S&amp;P Idas specialtecken artikel test 1 !“#€%&amp;/()=? Co&amp;O',
            'TeaserRaw' => <<<ENDTEXT
<?xml version="1.0" encoding="UTF-8"?>
<object xmlns="http://www.infomaker.se/newsml/1.0" id="MjI4LDE1NywyMDMsOTQ"
        title="S&amp;P Idas specialtecken artikel test 1 !“#€%&amp;/()=? Co&amp;O &lt;script&gt; window.alert(“sometext”);&lt;/script&gt;"
        type="x-im/teaser">
    <data>
        <text>S&amp;P Idas specialtecken artikel test 1 X@X o&amp;o &lt;script&gt; window.alert(“tryggve was right”);&lt;/script&gt;</text>
    </data>
</object>
ENDTEXT
        ]);
    }

    /**
     * Test getViewItems
     */
    public function testGetViewItems(): void
    {
        $item1 = RssItem::fromOcArticle($this->fakeOcArticle1, $this->itemSettings1);
        $viewItems1 = $item1->getViewItems();

        self::assertCount(8, $viewItems1);

        self::assertArrayHasKey('link', $viewItems1);
        self::assertArrayHasKey('guid', $viewItems1);
        self::assertArrayHasKey('title', $viewItems1);
        self::assertArrayHasKey('description', $viewItems1);
        self::assertArrayHasKey('creators', $viewItems1);
        self::assertArrayHasKey('pubdate', $viewItems1);
        self::assertArrayHasKey('images', $viewItems1);
        self::assertArrayHasKey('categories', $viewItems1);

        self::assertIsString($viewItems1['link']);
        self::assertIsString($viewItems1['guid']);
        self::assertIsString($viewItems1['title']);
        self::assertIsString($viewItems1['description']);
        self::assertInstanceOf(DateTimeImmutable::class, $viewItems1['pubdate']);
        self::assertIsArray($viewItems1['creators']);
        self::assertIsArray($viewItems1['images']);
        self::assertIsArray($viewItems1['categories']);

        $item2 = RssItem::fromOcArticle($this->fakeOcArticle1, $this->itemSettings2);
        $viewItems2 = $item2->getViewItems();

        self::assertCount(2, $viewItems2);

        self::assertArrayHasKey('link', $viewItems1);
        self::assertArrayHasKey('guid', $viewItems1);
    }

    /**
     * Test FromOcArticle() with maximal item settings.
     */
    public function testFromOcArticleWithMaximalItemSettings(): void
    {
        $item1 = RssItem::fromOcArticle($this->fakeOcArticle1, $this->itemSettings1);
        $xmlItem1 = self::asSimpleXml($item1);

        self::assertSame(6, count($xmlItem1));

        // Assert <link> element.
        self::assertSame(
            '<link>' . $this->fakeOcArticle1->get_permalink() . '</link>',
            $xmlItem1->link->asXML()
        );

        // Assert <guid> element.
        self::assertSame(
            '<guid isPermaLink="false">' . $this->fakeOcArticle1->get_value('uuid') . '</guid>',
            $xmlItem1->guid->asXML()
        );

        // Assert <title> element.
        self::assertSame('<title>Just nu: headlineitalic</title>', $xmlItem1->title->asXML());

        // todo assert description

        // Assert <dc:creator> elements.
        self::assertSame('Ida Bergkvist', $xmlItem1->children(self::DC_NAMESPACE)->creator[0]->__toString());
        self::assertSame('Olof Carlerud', $xmlItem1->children(self::DC_NAMESPACE)->creator[1]->__toString());

        // Assert <pubDate> element.
        self::assertSame('<pubDate>Tue, 13 Oct 2020 11:02:00 +0000</pubDate>', $xmlItem1->pubDate->asXML());

        // Assert <category> element.
        self::assertSame(1, count($xmlItem1->category));
        self::assertSame('<category>Sport</category>', $xmlItem1->category->asXML());

        // Assert <media:thumbnail> element.
        $thumbnail = $xmlItem1->children(self::MEDIA_NAMESPACE)->thumbnail;
        self::assertSame('88', $thumbnail->attributes()['width']->__toString());
        self::assertSame('50', $thumbnail->attributes()['height']->__toString());
        $url = $thumbnail->attributes()['url']->__toString();
        self::assertStringContainsString('uuid=27439dc5-97fe-5cfe-895a-72c9721eb3de', $url);

        // Assert that thumbnail is implementing cropping properly.
        self::assertStringContainsString('crop_w=0.35625', $url);
        self::assertStringContainsString('crop_h=0.44628', $url);
        self::assertStringContainsString('x=0.43875', $url);
        self::assertStringContainsString('y=0.09366', $url);

        // Test another article.
        $item2 = RssItem::fromOcArticle($this->fakeOcArticle2, $this->itemSettings1);
        $xmlItem2 = self::asSimpleXml($item2);

        self::assertSame(6, count($xmlItem2));

        // Assert <link> element.
        self::assertSame(
            '<link>' . $this->fakeOcArticle2->get_permalink() . '</link>',
            $xmlItem2->link->asXML()
        );

        // Assert <guid> element.
        self::assertSame(
            '<guid isPermaLink="false">' . $this->fakeOcArticle2->get_value('uuid') . '</guid>',
            $xmlItem2->guid->asXML()
        );

        // Assert <title> element.
        self::assertSame(
            '<title>S&amp;P Idas specialtecken artikel test 1 !“#€%&amp;/()=? Co&amp;O</title>',
            $xmlItem2->title->asXML()
        );

        // Assert <description> element.
        self::assertStringMatchesFormat(
            '<description>%w<![CDATA[%wS&amp;P Idas specialtecken artikel test 1 X@X o&amp;o &amp;lt;script&amp;gt; window.alert(“tryggve was right”);&amp;lt;/script&amp;gt;%w]]>%w</description>',
            $xmlItem2->description->asXML()
        );

        // Assert (no) <dc:creator> elements.
        self::assertFalse(isset($xmlItem2->children(self::DC_NAMESPACE)->creators));

        // Assert (no) <pubDate> element.
        self::assertFalse(isset($xmlItem2->pubDate));

        // Assert <category> element(s).
        self::assertSame(2, count($xmlItem2->category));
        self::assertSame('<category>Världen</category>', $xmlItem2->category[0]->asXML());
        self::assertSame('<category>Rymden</category>', $xmlItem2->category[1]->asXML());

        // Assert (no) <media:thumbnail> element.
        self::assertFalse(isset($xmlItem2->children(self::MEDIA_NAMESPACE)->thumbnail));
    }

    /**
     * Test FromOcArticle() with minimal item settings.
     */
    public function testFromOcArticleWithMinimalSettings(): void
    {
        $item1 = RssItem::fromOcArticle($this->fakeOcArticle1, $this->itemSettings2);
        $xmlItem1 = self::asSimpleXml($item1);

        self::assertSame(2, count($xmlItem1->children()));

        // Assert <link> element.
        self::assertTrue(isset($xmlItem1->link));
        self::assertSame($this->fakeOcArticle1->get_permalink(), $xmlItem1->link->__toString());

        // Assert <guid> element.
        self::assertTrue(isset($xmlItem1->guid));
        self::assertSame($this->fakeOcArticle1->get_value('uuid'), $xmlItem1->guid->__toString());
        self::assertSame(1, count($xmlItem1->guid->attributes()));
        self::assertSame('false', $xmlItem1->guid['isPermaLink']->__toString());

        // Test another article.
        $item2 = RssItem::fromOcArticle($this->fakeOcArticle2, $this->itemSettings2);
        $xmlItem2 = self::asSimpleXml($item2);

        // Assert <link> element.
        self::assertTrue(isset($xmlItem2->link));
        self::assertSame($this->fakeOcArticle2->get_permalink(), $xmlItem2->link->__toString());

        // Assert <guid> element.
        self::assertTrue(isset($xmlItem2->guid));
        self::assertSame($this->fakeOcArticle2->get_value('uuid'), $xmlItem2->guid->__toString());
        self::assertSame(1, count($xmlItem2->guid->attributes()));
        self::assertSame('false', $xmlItem2->guid['isPermaLink']->__toString());
    }

    public function setUp(): void
    {
        parent::setUp();

        Imengine::setup($this->imengineServerUrl);

        $this->itemSettings1 = self::getItemSettings1();
        $this->itemSettings2 = self::getItemSettings2();
        $this->fakeOcArticle1 = self::getFakeOcArticle1();
        $this->fakeOcArticle2 = self::getFakeOcArticle2();
    }

    /**
     * Run the item through the default template, and return as SimpleXmlElement, for easy testing.
     *
     * @param RssItemInterface $item
     *
     * @return SimpleXMLElement
     */
    private static function asSimpleXml(RssItemInterface $item): SimpleXMLElement
    {
        $xml = View::generate(self::$template, $item->getViewItems());

        return new SimpleXMLElement($xml);
    }

    private const DC_NAMESPACE = 'http://purl.org/dc/elements/1.1/';

    private const MEDIA_NAMESPACE = 'http://search.yahoo.com/mrss/';

    /**
     * @var string
     */
    private static $template = '@rssFeedsPlugin/views/rss-item';

    /**
     * @var string
     */
    private $imengineServerUrl = 'https://example.com/imengine/';

    /**
     * @var array
     */
    private $itemSettings1;

    /**
     * @var array
     */
    private $itemSettings2;

    /**
     * @var FakeOcArticle
     */
    private $fakeOcArticle1;

    /**
     * @var FakeOcArticle
     */
    private $fakeOcArticle2;
}
