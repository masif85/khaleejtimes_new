<?php

/** @noinspection AccessModifierPresentedInspection */
/** @noinspection ReturnTypeCanBeDeclaredInspection */

namespace Spec\Everyware\ProjectPlugin\Html;

use Everyware\ProjectPlugin\Components\SettingsField;
use Everyware\ProjectPlugin\Html\HtmlBuilder;
use Infomaker\Everyware\Support\Date;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FormBuilderSpec extends ObjectBehavior
{
    private $success = 'OK';

    /**
     * @var HtmlBuilder
     */
    private $htmlBuilder;

    private function formatHtml($html)
    {
        return trim(preg_replace('~>\s+<~', '><', $html));
    }

    protected function singleTagShouldBeCalledWith($tag, array $attributes = [])
    {
        $this->htmlBuilder->singleTag($tag, $attributes)->shouldBeCalled()->willReturn($this->success);

        return $this->success;
    }

    protected function inputShouldBeCalledWith($type, SettingsField $data, array $options = [])
    {
        $options = array_replace($data->toArray(), $options);
        $options['type'] = $type;

        return $this->singleTagShouldBeCalledWith('input', $options);
    }

    function let(HtmlBuilder $htmlBuilder)
    {
        $this->htmlBuilder = $htmlBuilder;
        $this->beConstructedWith($this->htmlBuilder);
    }

    function it_can_open_a_form()
    {
        $this->htmlBuilder->singleTag('form', [
            'method' => 'POST'
        ])->shouldBeCalled()->willReturn($this->success);

        $this->open()->shouldReturn($this->success);
    }

    function it_can_open_a_form_with_options()
    {
        $result = $this->singleTagShouldBeCalledWith('form', [
            'method' => 'GET',
            'action' => '/'
        ]);

        $this->open(['action' => '/', 'method' => 'GET'])->shouldReturn($result);
    }

    function it_can_close_the_form()
    {
        $this->htmlBuilder
            ->toHtmlString('</form>')
            ->shouldBeCalled()
            ->willReturn($this->success);

        $this->close()->shouldReturn($this->success);
    }

    function it_can_create_label()
    {
        $labelTitle = 'Label Text';
        $settingsField = new SettingsField('headline', '', 'prefix');

        $this->htmlBuilder
            ->tag('label', $labelTitle, [
                'for' => $settingsField->id()
            ])
            ->shouldBeCalled()
            ->willReturn($this->success);

        $this->label(new SettingsField('headline', '', 'prefix'), $labelTitle)->shouldReturn($this->success);
    }

    function it_can_create_label_with_attributes()
    {
        $labelTitle = 'Label Text';
        $settingsField = new SettingsField('headline', '', 'prefix');

        $this->htmlBuilder
            ->tag('label', $labelTitle, [
                'for' => $settingsField->id(),
                'title' => 'title text',
                'class' => ['label-class']
            ])
            ->shouldBeCalled()
            ->willReturn($this->success);

        $this->label($settingsField, 'Label Text', ['title' => 'title text', 'class' => ['label-class']], false)->shouldReturn($this->success);
    }

    function it_can_create_input_field()
    {
        $settingsField = new SettingsField('headline', 'Headline Text', 'prefix');

        $result = $this->singleTagShouldBeCalledWith('input', array_replace($settingsField->toArray(), ['type' => 'text']));
        $this->input('text', $settingsField)->shouldReturn($result);
    }

    function it_can_create_input_field_with_attributes()
    {
        $settingsField = new SettingsField('headline', 'Headline Text', 'prefix');

        $result = $this->singleTagShouldBeCalledWith('input', array_replace($settingsField->toArray(), [
            'type' => 'text',
            'class' => 'some-class'
        ]));

        $this->input('text', $settingsField, ['class' => 'some-class'])->shouldReturn($result);
    }

    function it_can_create_textarea()
    {
        $settingsField = new SettingsField('description', 'Random Text', 'prefix');

        $this->htmlBuilder
            ->tag('textarea', $settingsField->value(true), [
                'name' => $settingsField->name(),
                'id' => $settingsField->id(),
                'rows' => 3,
                'cols' => 40
            ])
            ->shouldBeCalled()
            ->willReturn($this->success);

        $this->textarea($settingsField)->shouldReturn($this->success);
    }

    function it_can_create_textarea_with_attributes()
    {
        $settingsField = new SettingsField('description', 'Random Text', 'prefix');

        $this->htmlBuilder
            ->tag('textarea', $settingsField->value(true), [
                'name' => $settingsField->name(),
                'id' => 'textarea-id',
                'rows' => 3,
                'cols' => 40
            ])
            ->shouldBeCalled()
            ->willReturn($this->success);

        $this->textarea($settingsField, ['id' => 'textarea-id'])->shouldReturn($this->success);
    }

    function it_can_create_button()
    {
        $inputValue = 'Random text';

        $this->htmlBuilder
            ->tag('button', $inputValue, ['type' => 'button'])
            ->shouldBeCalled()
            ->willReturn($this->success);

        $this->button($inputValue)->shouldReturn($this->success);
    }

    function it_can_create_button_with_attributes()
    {
        $inputValue = 'Random text';
        $options = ['name' => 'name'];

        $this->htmlBuilder
            ->tag('button', $inputValue, array_replace(['type' => 'button'], $options))
            ->shouldBeCalled()
            ->willReturn($this->success);

        $this->button($inputValue, $options)->shouldReturn($this->success);
    }

    function it_can_a_date_input()
    {
        $date = Date::create(2019, 01, 01);
        $settingsField = new SettingsField('pubdate', $date->toDateTimeString(), 'prefix');

        $result = $this->inputShouldBeCalledWith('date', $settingsField, ['value' => $date->format('Y-m-d')]);
        $this->date($settingsField)->shouldReturn($result);
    }

    function it_can_a_create_datetime_input()
    {
        $date = Date::create(2019, 01, 01);
        $settingsField = new SettingsField('pubdate', $date->toDateTimeString(), 'prefix');

        $result = $this->inputShouldBeCalledWith('datetime', $settingsField, ['value' => $date->format(Date::RFC3339)]);
        $this->datetime($settingsField)->shouldReturn($result);
    }

    function it_can_create_checked_checkbox_input()
    {
        $this->htmlBuilder->singleTag('input', [
            'type' => 'checkbox',
            'id' => 'prefix-checked',
            'name' => 'prefix[checked]',
            'value' => 'on',
            'checked' => 'checked'
        ])->shouldBeCalled();

        $this->checkbox( new SettingsField('checked', true, 'prefix'));
    }

    function it_can_create_unchecked_checkbox_input()
    {
        $this->htmlBuilder->singleTag('input', [
            'type' => 'checkbox',
            'id' => 'prefix-checked',
            'name' => 'prefix[checked]',
            'value' => 'on'
        ])->shouldBeCalled();


        $this->checkbox(new SettingsField('checked', false, 'prefix'));
    }

    function it_can_create_checked_checkbox_input_using_string()
    {
        $this->htmlBuilder->singleTag('input', [
            'type' => 'checkbox',
            'id' => 'prefix-checked',
            'name' => 'prefix[checked]',
            'value' => 'on',
            'checked' => 'checked'
        ])->shouldBeCalled();

        $this->checkbox( new SettingsField('checked', 'on', 'prefix'));
    }

    function it_can_create_checked_radio_input()
    {
        $this->htmlBuilder->singleTag('input', [
            'type' => 'radio',
            'name' => 'prefix[checked]',
            'value' => 'value',
            'checked' => 'checked'
        ])->shouldBeCalled();

        $this->radio(new SettingsField('checked', 'value', 'prefix'), 'value');
    }

    function it_can_create_unchecked_radio_input()
    {
        $this->htmlBuilder->singleTag('input', [
            'type' => 'radio',
            'name' => 'prefix[checked]',
            'value' => 'another_value'
        ])->shouldBeCalled();

        $this->radio(new SettingsField('checked', 'value', 'prefix'), 'another_value');
    }

    function it_can_create_select_from_list()
    {
        $settingsField = new SettingsField('page', 1, 'prefix');
        $list = [
            ['value' => 1, 'text' => 'Page 1'],
            ['value' => 2, 'text' => 'Page 2'],
            ['value' => 3, 'text' => 'Page 3'],
            ['value' => 4, 'text' => 'Page 4'],
        ];

        $this->htmlBuilder
            ->select($list, $settingsField->value(), [
                'id' => $settingsField->id(),
                'name' => $settingsField->name()
            ])
            ->shouldBeCalled()
            ->willReturn($this->success);

        $this->select($settingsField, $list)->shouldReturn($this->success);
    }

    function it_adds_a_placeholder_option_instead_of_attribute()
    {
        $settingsField = new SettingsField('page', 1, 'prefix');
        $list = [
            ['value' => 1, 'text' => 'Page 1'],
            ['value' => 2, 'text' => 'Page 2'],
            ['value' => 3, 'text' => 'Page 3'],
            ['value' => 4, 'text' => 'Page 4'],
        ];

        $this->htmlBuilder
            ->select(array_merge([
                ['value' => '', 'text' => 'Placeholder text'],
            ], $list), $settingsField->value(), [
                'id' => $settingsField->id(),
                'name' => $settingsField->name()
            ])
            ->shouldBeCalled()
            ->willReturn($this->success);

        $this->select($settingsField, $list, ['placeholder' => 'Placeholder text'])->shouldReturn($this->success);
    }

    function it_can_create_tooltip_buttons()
    {
        $text = 'Random information';

        $this->htmlBuilder
            ->tag('button', '<i class="fa fa-question"></i>', [
                'type' => 'button',
                'class' => 'ew-tooltip',
                'data-placement' => 'left',
                'data-toggle' => 'tooltip',
                'title' => $text
            ])
            ->shouldBeCalled()
            ->willReturn($this->success);

        $this->tooltip($text)->shouldReturn($this->success);
    }

    function it_offers_positioning_of_tooltip_buttons()
    {
        $text = 'Random information';
        $position = 'right';

        $this->htmlBuilder
            ->tag('button', '<i class="fa fa-question"></i>', [
                'type' => 'button',
                'class' => 'ew-tooltip',
                'data-placement' => $position,
                'data-toggle' => 'tooltip',
                'title' => $text
            ])
            ->shouldBeCalled()
            ->willReturn($this->success);

        $this->tooltip($text, $position)->shouldReturn($this->success);
    }

    function it_supports_html_in_tooltip()
    {
        $text = '<p>Random information</p>';
        $position = 'right';

        $this->htmlBuilder
            ->tag('button', '<i class="fa fa-question"></i>', [
                'type' => 'button',
                'class' => 'ew-tooltip',
                'data-placement' => $position,
                'data-toggle' => 'tooltip',
                'data-html' => 'true',
                'title' => $text
            ])
            ->shouldBeCalled()
            ->willReturn($this->success);

        $this->tooltip($text, $position)->shouldReturn($this->success);
    }

    function it_offers_alternative_tooltp_icon()
    {
        $text = '<p>Random information</p>';
        $position = 'right';
        $icon = 'custom-icon';

        $this->htmlBuilder
            ->tag('button', $icon, [
                'type' => 'button',
                'class' => 'ew-tooltip',
                'data-placement' => $position,
                'data-toggle' => 'tooltip',
                'data-html' => 'true',
                'title' => $text
            ])
            ->shouldBeCalled()
            ->willReturn($this->success);

        $this->tooltip($text, $position, $icon)->shouldReturn($this->success);
    }

    function it_can_create_a_toggle_button_for_on_off_settings()
    {
        $settingsField = new SettingsField('show stuff', false, 'prefix');
        $id = $settingsField->id();

        $offLabel = 'offLabel';
        $onLabel = 'offLabel';
        $toggleLabel = 'toggleLabel';
        $toggleCheckbox = 'toggleCheckbox';
        $toggleOutput = '';

        $this->htmlBuilder->tag('label', 'off', [
            'for' => $id,
            'class' => 'checkbox-toggle-not-checked'
        ])
            ->shouldBeCalled()
            ->willReturn($offLabel);

        $this->htmlBuilder->tag('label', '', [
            'for' => $id
        ])
            ->shouldBeCalled()
            ->willReturn($toggleLabel);

        $this->htmlBuilder->singleTag('input',
            array_replace($settingsField->toArray(), [
                'type' => 'checkbox',
                'class' => 'checkbox',
                'value' => 'on'
            ]))
            ->shouldBeCalled()
            ->willReturn($toggleCheckbox);

        $this->htmlBuilder->tag('label', 'on', [
            'for' => $id,
            'class' => 'checkbox-toggle-checked'
        ])
            ->shouldBeCalled()
            ->willReturn($onLabel);

        $toggleOutput .= $offLabel;
        $toggleOutput .= '<div class="checkbox-toggle">' . $toggleCheckbox . $toggleLabel .'</div>';
        $toggleOutput .= $onLabel;

        $this->htmlBuilder->tag('div', $toggleOutput, ['class' => 'checkbox-toggle-wrapper'])
            ->shouldBeCalled()
            ->willReturn($this->success);

        $this->toggleButton($settingsField)->shouldReturn($this->success);
    }
}
