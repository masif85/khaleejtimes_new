<?php

namespace Spec\Everyware\ProjectPlugin\Components\SettingsProviders;

use Everyware\ProjectPlugin\Components\Contracts\SettingsProvider;
use Everyware\ProjectPlugin\Components\SettingsProviders\SimpleSettingsProvider;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SimpleSettingsProviderSpec extends ObjectBehavior
{
    private $settings = [
        'test' => true
    ];

    private $fields = [
        'test' => true
    ];

    function let()
    {
        $this->beConstructedWith($this->fields);
        $this->shouldImplement(SettingsProvider::class);

        $this->save($this->settings);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SimpleSettingsProvider::class);
    }

    function it_supports_getting_settings()
    {
        $this->get()->shouldReturn($this->settings);
    }

    function it_supports_getting_single_setting()
    {
        $this->getSingle('test')->shouldReturn(true);
    }

    function it_offers_a_way_to_fetch_the_required_fields()
    {
        $this->requiredFields()->shouldReturn($this->fields);
    }
}
