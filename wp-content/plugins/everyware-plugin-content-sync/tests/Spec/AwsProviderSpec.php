<?php

namespace Spec\Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\AwsProvider;
use PhpSpec\ObjectBehavior;

class AwsProviderSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(AwsProvider::class);
    }

    public function it_should_return_empty_string_if_region_cant_be_extracted()
    {
        $this->getAwsRegion()->shouldReturn('');
    }

    public function it_can_extract_the_correct_region_using_the_rds_host()
    {
        define('DB_HOST', 'test-ljblt.pkougutd.eu-west-1.rds.amazonaws.com');
        $this->getAwsRegion()->shouldReturn('eu-west-1');
    }

    public function it_can_extract_the_correct_region_using_a_provided_url()
    {
        $this->getAwsRegion('test-ljblt.pkougutd.eu-north-1.rds.amazonaws.com')->shouldReturn('eu-north-1');
    }
}
