<?php

namespace Spec\Everyware\ProjectPlugin\Components;

use Everyware\ProjectPlugin\Components\SettingsField;
use Everyware\ProjectPlugin\Components\SettingsHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SettingsHandlerSpec extends ObjectBehavior
{
    private $formPrefix = 'field_prefix';
    private $fields = [
        'headline' => '',
        'has_headline' => false,
        'links' => []
    ];

    public function let()
    {
        $this->beConstructedWith($this->formPrefix);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SettingsHandler::class);
    }

    public function it_should_generate_proper_form_data()
    {
        $fields = $this->generateFormData($this->fields);

        $fields->shouldHaveCount(count($this->fields));

        foreach ($fields as $field) {
            $field->shouldBeAnInstanceOf(SettingsField::class);
        }
    }

    public function it_supports_overriding_of_fields_uppon_getting_data()
    {
        $fields = $this->generateFormData([
            'headline' => 'new Headline',
            'has_headline' => true
        ]);

        $fields->shouldHaveCount(2);

        foreach ($fields as $field) {
            $field->shouldBeAnInstanceOf(SettingsField::class);
        }
    }

    public function it_has_support_for_list_fields()
    {
        $fields = $this->generateFormData([
            'links' => ['first_link'],
            'links2' => [
                ['name' => 'first_link']
            ]
        ]);

        $fields->shouldHaveCount(2);

        foreach ($fields as $field) {
            $field->shouldBeAnInstanceOf(SettingsField::class);
        }
    }

    function it_offers_getter_for_formprefix()
    {
        $this->getFormPrefix()->shouldReturn($this->formPrefix);
    }

    function it_offers_grouping_off_fields()
    {
        $group = 'group';
        $fields = $this->generateGroupedFormData($group, $this->fields);
        $fields->shouldHaveCount(count($this->fields));

        foreach ($fields as $field) {
            $field->shouldBeAnInstanceOf(SettingsField::class);
        }
    }
}
