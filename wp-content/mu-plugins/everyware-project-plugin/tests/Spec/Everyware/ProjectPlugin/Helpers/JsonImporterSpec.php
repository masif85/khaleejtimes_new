<?php

namespace Spec\Everyware\ProjectPlugin\Helpers;

use Everyware\ProjectPlugin\Helpers\JsonImporter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class JsonImporterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(JsonImporter::class);
    }

    public function it_can_determine_if_jason_has_been_sent()
    {
        $this->isImported()->shouldReturn(false);
    }

    public function it_should_have_a_default_value_to_listen_for()
    {
        $_POST[JsonImporter::POST_KEY] = '';

        $this->isImported()->shouldReturn(true);
    }

    public function it_can_convert_json_to_array()
    {
        $_POST[JsonImporter::POST_KEY] = '{"headline":"something", "boolValue": "true"}';

        $this->getJson()->shouldReturn([
            'headline' => 'something',
            'boolValue' => 'true',
        ]);
    }

    public function it_can_fetch_the_sent_json()
    {
        $json = '{"headline":"something", "boolValue": "true"}';

        $_POST[JsonImporter::POST_KEY] = $json;

        $this->getRawJson()->shouldReturn($json);
    }

    public function it_offers_the_option_for_custom_post_key()
    {
        $submit_name = 'custom-submit-name';

        $this->beConstructedWith($submit_name);

        $_POST[$submit_name] = '{"headline":"something", "boolValue": "true"}';

        $this->isImported()->shouldReturn(true);

        $this->getJson()->shouldReturn([
            'headline' => 'something',
            'boolValue' => 'true',
        ]);
    }
}
