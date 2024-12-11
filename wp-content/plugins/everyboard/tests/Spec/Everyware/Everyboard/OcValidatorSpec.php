<?php

namespace Spec\Everyware\Everyboard;

use Everyware\Everyboard\Exceptions\InvalidListUuid;
use Everyware\Everyboard\Exceptions\InvalidQueryOrUnauthorized;
use Everyware\Everyboard\OcArticleProvider;
use Everyware\Everyboard\OcListAdapter;
use Everyware\Everyboard\OcValidator;
use PhpSpec\ObjectBehavior;

class OcValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(OcValidator::class);
    }

    public function it_can_validate_correct_queries(OcArticleProvider $provider)
    {
        $query = '*:*';
        $provider->testQuery($query)->willReturn(50);
        $this->validateOcQuery($query, $provider)->shouldEqual(true);
    }

    public function it_can_fail_invalid_queries(OcArticleProvider $provider)
    {

        $this->validateOcQuery('', $provider)->shouldEqual(false);
        $this->clearMessages();

        $query = 'Invalid Query';

        $provider->testQuery($query)->willThrow(new InvalidQueryOrUnauthorized('Error Message'));
        $this->validateOcQuery($query, $provider)->shouldEqual(false);
    }

    public function it_can_serve_validation_messages_after_query_validation(OcArticleProvider $provider)
    {
        $query = 'Invalid Query';
        $message= 'Error Message';

        $provider->testQuery($query)->willThrow(new InvalidQueryOrUnauthorized($message));

        $this->validateOcQuery($query, $provider)->shouldEqual(false);

        $this->getValidationMessages()->shouldReturn([$message]);
    }

    public function it_can_serve_article_count_after_query_validation(OcArticleProvider $provider)
    {
        $query = '*:*';
        $provider->testQuery($query)->willReturn(50);
        $this->validateOcQuery($query, $provider)->shouldEqual(true);

        $this->getArticleCount()->shouldReturn(50);
    }

    public function it_can_have_zero_article_count_after_failed_query_validation(OcArticleProvider $provider)
    {
        $this->validateOcQuery('', $provider)->shouldEqual(false);

        $query = 'Invalid Query';

        $provider->testQuery($query)->willThrow(InvalidQueryOrUnauthorized::class);
        $this->validateOcQuery($query, $provider)->shouldEqual(false);

        $this->getArticleCount()->shouldReturn(0);
    }

    public function it_can_validate_correct_lists(OcListAdapter $adapter)
    {
        $uuid = 'list uuid';
        $adapter->get_article_uuids_or_fail($uuid)->willReturn(range(0,50));

        $this->validateList($uuid, $adapter)->shouldEqual(true);
    }

    public function it_can_fail_invalid_lists(OcListAdapter $adapter)
    {
        $uuid = '';
        $adapter->get_article_uuids_or_fail($uuid)->willThrow(new InvalidListUuid('Empty uuid'));

        $this->validateList($uuid, $adapter)->shouldEqual(false);
    }

    public function it_will_fail_if_no_articles_can_be_fetched_from_lists(OcListAdapter $adapter)
    {
        $uuid = '';
        $adapter->get_article_uuids_or_fail($uuid)->willReturn([]);

        $this->validateList($uuid, $adapter)->shouldEqual(false);
    }

    public function it_can_have_zero_article_count_after_failed_list_validation(OcListAdapter $adapter)
    {
        $uuid = '';
        $adapter->get_article_uuids_or_fail($uuid)->willReturn([]);

        $this->validateList($uuid, $adapter)->shouldEqual(false);

        $this->getArticleCount()->shouldReturn(0);
    }
}
