<?php

namespace Spec\Everyware\ProjectPlugin\Components;

use Everyware\ProjectPlugin\Components\SettingsField;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SettingsFieldSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('links[0][link_url]', 'link url');
        $this->shouldHaveType(SettingsField::class);
    }

    function it_can_create_proper_id_from_name()
    {
        $this->beConstructedWith('links[0][link_url]', 'link url');

        $this->id()->shouldReturn('links-0-link_url');
    }

    function it_hass_support_for_an_escaped_version_of_the_value()
    {
        $this->beConstructedWith('comment', '<p>paragraph</p>');

        $this->value(true)->shouldReturn('&lt;p&gt;paragraph&lt;/p&gt;');
    }

    function it_has_support_for_prefix_for_name()
    {
        $prefix = 'field-prefix';
        $this->beConstructedWith('links[0][link_url]', 'link url', $prefix);

        $this->name()->shouldReturn($prefix . '[links][0][link_url]');
    }

    function it_has_support_for_prefix_for_id()
    {
        $prefix = 'field-prefix';
        $this->beConstructedWith('links[0][link_url]', 'link url', $prefix);

        $this->id()->shouldReturn($prefix . '-links-0-link_url');
    }

    function it_supports_grouping_of_field()
    {
        $prefix = 'field-prefix';
        $this->beConstructedWith('links[0][link_url]', 'link url', $prefix.'[group]');

        $this->name()->shouldReturn($prefix . '[group][links][0][link_url]');
        $this->id()->shouldReturn($prefix . '-group-links-0-link_url');
    }
}
