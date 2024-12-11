<?php

namespace Spec\Everyware\ProjectPlugin\Components;

use Everyware\ProjectPlugin\Components\ComponentSettingsRepository;
use Everyware\ProjectPlugin\Components\Contracts\SettingsProvider;
use Everyware\ProjectPlugin\Components\Contracts\SettingsRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ComponentSettingsRepositorySpec extends ObjectBehavior
{
    private $fields = [
        'headline' => 'test',
        'startup_field' => true
    ];

    function let(SettingsProvider $provider)
    {
        $provider
            ->requiredFields()
            ->shouldBeCalled()
            ->willReturn($this->fields);
        $this->beConstructedWith($provider);

        $this->shouldImplement(SettingsRepository::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComponentSettingsRepository::class);
    }

    function it_offers_a_way_to_retrieve_stored_settings(SettingsProvider $provider)
    {
        $provider
            ->get()
            ->shouldBeCalled()
            ->willReturn([]);

        $this->get()->shouldReturn($this->fields);
    }

    function it_offers_required_fields(SettingsProvider $provider)
    {
        $requiredFields = [
            'new_field' => 'test',
            'test' => true
        ];

        foreach ($requiredFields as $name => $value) {
            $this->addRequiredField($name, $value);
        }

        $provider
            ->get()
            ->shouldBeCalled()
            ->willReturn([]);

        $this->get()->shouldReturn(array_replace($this->fields, $requiredFields));
    }

    function it_offers_a_way_to_retrieve_single_stored_setting(SettingsProvider $provider)
    {
        $key = 'headline';
        $result = 'ok';
        $provider
            ->getSingle($key)
            ->shouldBeCalled()
            ->willReturn($result);

        $this->getValue($key)->shouldReturn($result);
    }

    function it_can_merge_settings_into_valid_data()
    {
        $fields = [
            'new_field' => 'test',
            'test' => true,
            'headline' => 'test_changed',
        ];

        $result = array_replace($this->fields, [
            'headline' => 'test_changed'
        ]);

        $this->getValidSettings($fields)->shouldReturn($result);
    }

    function it_saves_data_in_the_collectionDB(SettingsProvider $provider)
    {
        $fields = [
            'new_field' => 'test',
            'test' => true,
            'headline' => 'test_changed',
        ];

        $provider
            ->save($this->getValidSettings($fields))
            ->shouldBeCalled()
            ->willReturn(true);

        $this->save($fields)->shouldReturn(true);
    }

    function it_can_update_settings(SettingsProvider $provider)
    {
        $newSettings = [
            'new_field' => 'test',
            'test' => true,
            'headline' => 'test_changed',
        ];

        $fields = array_replace($this->fields,$newSettings);

        $provider
            ->save($this->getValidSettings($fields))
            ->shouldBeCalled()
            ->willReturn(true);

        $this->update($newSettings, $this->fields)->shouldReturn(true);
    }
}
