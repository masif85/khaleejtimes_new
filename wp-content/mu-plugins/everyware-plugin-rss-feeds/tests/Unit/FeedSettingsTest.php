<?php declare(strict_types=1);


namespace Unit\Everyware\RssFeeds;


use Everyware\RssFeeds\FeedSettings;
use Everyware\RssFeeds\OcApiHandler;
use Everyware\RssFeeds\OcApiResponse;

class FeedSettingsTest extends RssFeedsTestCase
{
    public function testValidateOcList(): void
    {
        $_REQUEST['uuid'] = 'abc-123';
        $responseArray = FeedSettings::validateOcList($this->ocApiHandler)->toArray();

        self::assertEquals(OcApiResponse::RESPONSE_TYPE_OK, $responseArray['responseType']);
        self::assertEquals('Result', $responseArray['responseSubject']);
        self::assertEquals('4 articles', $responseArray['response']);

        $_REQUEST['uuid'] = 'jkl-012';
        $responseArray = FeedSettings::validateOcList($this->ocApiHandler)->toArray();

        self::assertEquals(OcApiResponse::RESPONSE_TYPE_WARNING, $responseArray['responseType']);
        self::assertEquals('Warning', $responseArray['responseSubject']);
        self::assertEquals('No articles in list', $responseArray['response']);

        $_REQUEST['uuid'] = 'invalid UUID #¤%&/()=';
        $responseArray = FeedSettings::validateOcList($this->ocApiHandler)->toArray();

        self::assertEquals(OcApiResponse::RESPONSE_TYPE_ERROR, $responseArray['responseType']);
        self::assertEquals('Error', $responseArray['responseSubject']);
        self::assertEquals('Invalid UUID', $responseArray['response']);
    }

    public function testValidateOcQuery(): void
    {
        $_REQUEST['query'] = '*:*';
        $_REQUEST['start'] = '0';
        $_REQUEST['limit'] = '100';
        $responseArray = FeedSettings::validateOcQuery($this->ocApiHandler)->toArray();

        self::assertEquals(OcApiResponse::RESPONSE_TYPE_OK, $responseArray['responseType']);
        self::assertEquals('Result', $responseArray['responseSubject']);
        self::assertEquals('4 articles', $responseArray['response']);

        $_REQUEST['query'] = '*:*';
        $_REQUEST['start'] = '10';
        $responseArray = FeedSettings::validateOcQuery($this->ocApiHandler)->toArray();

        self::assertEquals(OcApiResponse::RESPONSE_TYPE_WARNING, $responseArray['responseType']);
        self::assertEquals('Warning', $responseArray['responseSubject']);
        self::assertEquals('No articles found', $responseArray['response']);

        $_REQUEST['query'] = 'obviously invalid query, #¤%&/()';
        $responseArray = FeedSettings::validateOcQuery($this->ocApiHandler)->toArray();

        self::assertEquals(OcApiResponse::RESPONSE_TYPE_ERROR, $responseArray['responseType']);
        self::assertEquals('Error', $responseArray['responseSubject']);
        self::assertEquals('Invalid query, or unauthorized', $responseArray['response']);
    }

    protected function setUp(): void
    {
        $this->ocApiHandler = new OcApiHandler();
        $this->ocApiHandler->setDefaultApi(new FakeOcAPI());
    }

    /**
     * @var OcApiHandler
     */
    private $ocApiHandler;
}
