<?php declare(strict_types=1);


namespace Unit\Everyware\RssFeeds;


use Everyware\RssFeeds\Exceptions\InvalidListUuid;
use Everyware\RssFeeds\Exceptions\InvalidQueryOrUnauthorized;
use Everyware\RssFeeds\Exceptions\InvalidSorting;
use Everyware\RssFeeds\OcApiHandler;

class OcApiHandlerTest extends RssFeedsTestCase
{
    public function testGetLists(): void
    {
        $lists = $this->ocApiHandler->getLists();

        self::assertSame([
            'abc-123' => 'All articles',
            'def-456' => 'All articles, reverse order',
            'ghi-789' => 'Middle articles',
            'jkl-012' => 'No articles'
        ], $lists);
    }

    public function testGetSortOptions(): void
    {
        $sortOptions = $this->ocApiHandler->getSortOptions();

        self::assertObjectHasAttribute('sortings', $sortOptions);
        self::assertCount(1, $sortOptions->sortings);

        $sorting1 = $sortOptions->sortings[0];
        self::assertObjectHasAttribute('name', $sorting1);
        self::assertIsString($sorting1->name);
        self::assertObjectHasAttribute('sortIndexFields', $sorting1);
        self::assertIsArray($sorting1->sortIndexFields);
        self::assertCount(1, $sorting1->sortIndexFields);

        $field1 = $sorting1->sortIndexFields[0];
        self::assertObjectHasAttribute('indexField', $field1);
        self::assertIsString($field1->indexField);
        self::assertObjectHasAttribute('ascending', $field1);
        self::assertIsBool($field1->ascending);
    }

    public function testTestArticleCountForList(): void
    {
        $count1 = $this->ocApiHandler->testArticleCountForList('abc-123');
        self::assertSame(4, $count1);

        $count2 = $this->ocApiHandler->testArticleCountForList('def-456');
        self::assertSame(4, $count2);

        $count3 = $this->ocApiHandler->testArticleCountForList('ghi-789');
        self::assertSame(2, $count3);

        $this->expectException(InvalidListUuid::class);
        $this->ocApiHandler->testArticleCountForList('zzzzzz');
    }

    public function testGetArticlesByList(): void
    {
        $articles1 = $this->ocApiHandler->getArticlesByList('abc-123');
        self::assertCount(4, $articles1);
        self::assertSame('aaa', $articles1[0]->get_value('uuid'));
        self::assertSame('bbb', $articles1[1]->get_value('uuid'));
        self::assertSame('ccc', $articles1[2]->get_value('uuid'));
        self::assertSame('ddd', $articles1[3]->get_value('uuid'));

        $articles2 = $this->ocApiHandler->getArticlesByList('def-456');
        self::assertCount(4, $articles2);
        self::assertSame('ddd', $articles2[0]->get_value('uuid'));
        self::assertSame('ccc', $articles2[1]->get_value('uuid'));
        self::assertSame('bbb', $articles2[2]->get_value('uuid'));
        self::assertSame('aaa', $articles2[3]->get_value('uuid'));

        $articles3 = $this->ocApiHandler->getArticlesByList('ghi-789');
        self::assertCount(2, $articles3);
        self::assertSame('bbb', $articles3[0]->get_value('uuid'));
        self::assertSame('ccc', $articles3[1]->get_value('uuid'));

        $this->expectException(InvalidListUuid::class);
        $this->ocApiHandler->getArticlesByList('zzzzzz');
    }

    public function testTestArticleCountForQuery(): void
    {
        $count1 = $this->ocApiHandler->testArticleCountForQuery('*:*', 0, 100);
        self::assertSame(4, $count1);

        $count2 = $this->ocApiHandler->testArticleCountForQuery('*:*', 2, 100);
        self::assertSame(2, $count2);

        $count3 = $this->ocApiHandler->testArticleCountForQuery('*:*', 1, 3);
        self::assertSame(3, $count3);

        $this->expectException(InvalidQueryOrUnauthorized::class);
        $this->ocApiHandler->testArticleCountForQuery('superInvalidQuery >__<;;', 0, 100);
    }

    public function testGetArticlesByQueryWithStartAndLimit(): void
    {
        $articles1 = $this->ocApiHandler->getArticlesByQuery('*:*', null, 0, 100);
        self::assertCount(4, $articles1);
        self::assertSame('aaa', $articles1[0]->get_value('uuid'));
        self::assertSame('bbb', $articles1[1]->get_value('uuid'));
        self::assertSame('ccc', $articles1[2]->get_value('uuid'));
        self::assertSame('ddd', $articles1[3]->get_value('uuid'));

        $articles2 = $this->ocApiHandler->getArticlesByQuery('*:*', null, 2, 100);
        self::assertCount(2, $articles2);
        self::assertSame('ccc', $articles2[0]->get_value('uuid'));
        self::assertSame('ddd', $articles2[1]->get_value('uuid'));

        $articles3 = $this->ocApiHandler->getArticlesByQuery('*:*', null,1, 3);
        self::assertCount(3, $articles3);
        self::assertSame('bbb', $articles3[0]->get_value('uuid'));
        self::assertSame('ccc', $articles3[1]->get_value('uuid'));
        self::assertSame('ddd', $articles3[2]->get_value('uuid'));
    }

    public function testGetArticlesByQueryWithValidSorting(): void
    {
        $aValidSorting = FakeOcAPI::SORT_INDEX_NAME1;

        $articles = $this->ocApiHandler->getArticlesByQuery('*:*', $aValidSorting, 0, 100);
        self::assertCount(4, $articles);
        self::assertSame('ddd', $articles[0]->get_value('uuid'));
        self::assertSame('ccc', $articles[1]->get_value('uuid'));
        self::assertSame('bbb', $articles[2]->get_value('uuid'));
        self::assertSame('aaa', $articles[3]->get_value('uuid'));
    }

    public function testGetArticlesByQueryWithInvalidSorting(): void
    {
        $invalidSorting = 'sdlfjhskfhsdkjh';

        $this->expectException(InvalidSorting::class);
        $this->ocApiHandler->getArticlesByQuery('*:*', $invalidSorting,0, 100);
    }

    public function testGetArticlesByQueryWithInvalidQuery(): void
    {
        $this->expectException(InvalidQueryOrUnauthorized::class);
        $this->ocApiHandler->getArticlesByQuery('superInvalidQuery >__<;;', null, 0, 100);
    }

    protected function setUp(): void
    {
        $this->ocApiHandler = new OcApiHandler();
        $this->ocApiHandler->setDefaultApi(new FakeOcAPI());
    }

    private $ocApiHandler;
}
