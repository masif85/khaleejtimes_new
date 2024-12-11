<?php

namespace Spec\Everyware\ProjectPlugin\Components\Adapters;

use Everyware\ProjectPlugin\Components\Contracts\Admin;
use Everyware\ProjectPlugin\Components\Adapters\PluginSettingsTabAdapter;
use Everyware\ProjectPlugin\Interfaces\SettingsPageInterface;
use Spec\ComponentAdapterSpec;

class PluginSettingsTabAdapterSpec extends ComponentAdapterSpec
{
    private $postPrefix;

    private function postData($prefix, $data)
    {
        $this->postPrefix = $prefix;

        $_POST[$this->postPrefix] = $data;
    }

    function let(Admin $componentAdmin)
    {
        $this->beConstructedWith($componentAdmin);
        $this->shouldImplement(SettingsPageInterface::class);
    }

    function letGo()
    {
        unset($_POST[$this->postPrefix]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PluginSettingsTabAdapter::class);
    }

    function it_uses_the_component_name_for_title(Admin $componentAdmin)
    {
        $componentAdmin->getName()->shouldBeCalled()->willReturn('ok');
        $this->getTabTitle()->shouldReturn('ok');
    }

    function it_should_not_route_to_component_on_load(Admin $componentAdmin)
    {
        $storedSettings = [];
        $componentAdmin->getInputPrefix()->shouldBeCalled()->willReturn('prefix');
        $componentAdmin->getSettings()->shouldBeCalled()->willReturn($storedSettings);
        $componentAdmin->store($storedSettings)->shouldNotBeCalled();
        $componentAdmin->update($storedSettings)->shouldNotBeCalled();

        $this->onPageLoad();
    }

    function it_routes_to_component_store(Admin $componentAdmin)
    {
        $storedSettings = [];
        $this->postData('prefix', []);
        $componentAdmin->getInputPrefix()->shouldBeCalled()->willReturn('prefix');
        $componentAdmin->getSettings()->shouldBeCalled()->willReturn($storedSettings);
        $componentAdmin->store($storedSettings, [])->shouldBeCalled()->willReturn(true);

        $this->onPageLoad();
    }

    function it_routes_to_component_update(Admin $componentAdmin)
    {
        $storedSettings = ['has_settings'];
        $this->postData('prefix', []);
        $componentAdmin->getInputPrefix()->shouldBeCalled()->willReturn('prefix');
        $componentAdmin->getSettings()->shouldBeCalledTimes(2)->willReturn($storedSettings);
        $componentAdmin->update([], $storedSettings)->shouldBeCalled()->willReturn(true);

        $this->onPageLoad();
    }

    function it_routes_to_component_with_posted_data(Admin $componentAdmin)
    {
        $postedData = ['posted_data'];
        $storedSettings = ['has_settings'];

        $this->postData('prefix', $postedData);
        $componentAdmin->getInputPrefix()->shouldBeCalled()->willReturn('prefix');
        $componentAdmin->getSettings()->shouldBeCalledTimes(2)->willReturn($storedSettings);
        $componentAdmin->update($postedData, $storedSettings)->shouldBeCalled()->willReturn(true);

        $this->onPageLoad();
    }

    function it_routes_to_component_create_on_creation(Admin $componentAdmin)
    {
        $storedSettings = [];
        $componentAdmin->getSettings()->shouldBeCalled()->willReturn($storedSettings);
        $componentAdmin->create($storedSettings)->shouldBeCalled()->willReturn('ok');

        $this->pageContent()->shouldReturn('ok');
    }

    function it_routes_to_component_edit_if_stored_data_is_found(Admin $componentAdmin)
    {
        $storedSettings = ['has_settings'];
        $componentAdmin->getSettings()->shouldBeCalled()->willReturn($storedSettings);
        $componentAdmin->edit($storedSettings)->shouldBeCalled()->willReturn('ok');

        $this->pageContent()->shouldReturn('ok');
    }

    function it_wont_update_invalid_settings(Admin $componentAdmin)
    {
        $storedSettings = ['has_settings'];
        $this->postData('prefix', []);
        $componentAdmin->getInputPrefix()->shouldBeCalled()->willReturn('prefix');
        $componentAdmin->getSettings()->shouldBeCalledOnce()->willReturn($storedSettings);
        $componentAdmin->update([], $storedSettings)->shouldBeCalled()->willReturn(false);

        $this->onPageLoad();
    }

    function it_wont_store_invalid_settings(Admin $componentAdmin)
    {
        $storedSettings = [];

        $this->postData('prefix', []);
        $componentAdmin->getInputPrefix()->shouldBeCalled()->willReturn('prefix');
        $componentAdmin->getSettings()->shouldBeCalledOnce()->willReturn($storedSettings);
        $componentAdmin->store([], $storedSettings)->shouldBeCalled()->willReturn(false);

        $this->onPageLoad();
    }
}
