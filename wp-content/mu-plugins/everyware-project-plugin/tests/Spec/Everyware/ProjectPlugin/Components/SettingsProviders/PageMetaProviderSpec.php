<?php

namespace Spec\Everyware\ProjectPlugin\Components\SettingsProviders;

use Everyware\ProjectPlugin\Components\Contracts\SettingsProvider;
use Everyware\ProjectPlugin\Components\SettingsProviders\PageMetaProvider;
use Infomaker\Everyware\Base\Models\Page;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PageMetaProviderSpec extends ObjectBehavior
{

    private $metaKey = 'key';
    private $fields = [];

    function let(Page $page)
    {
        $this->beConstructedWith($this->metaKey, $page);

        $this->shouldImplement(SettingsProvider::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PageMetaProvider::class);
    }

    function it_offers_a_way_to_retrieve_stored_settings(Page $page)
    {
        $fields = [
            'headline' => 'test',
            'startup_field' => true
        ];

        $page
            ->getMeta($this->metaKey)
            ->shouldBeCalled()
            ->willReturn($fields);

        $this->get()->shouldReturn($fields);
    }

    function it_offers_a_way_to_retrieve_single_stored_setting(Page $page)
    {
        $fields = [
            'headline' => 'test',
            'startup_field' => true
        ];

        $page
            ->getMeta($this->metaKey)
            ->shouldBeCalled()
            ->willReturn($fields);

        $this->getSingle('headline')->shouldReturn('test');
    }

    function it_saves_data(Page $page)
    {
        $fields = [
            'new_field' => 'test',
            'test' => true,
            'headline' => 'test_changed',
        ];

        $page->updateMeta($this->metaKey, $fields)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->save($fields)->shouldReturn(true);
    }

    function it_offers_a_way_to_fetch_the_required_fields()
    {
        $this->requiredFields()->shouldReturn($this->fields);
    }
}
