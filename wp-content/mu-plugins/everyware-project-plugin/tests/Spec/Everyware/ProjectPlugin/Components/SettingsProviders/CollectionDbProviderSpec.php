<?php

namespace Spec\Everyware\ProjectPlugin\Component\SettingsProviders;

use Everyware\ProjectPlugin\Components\SettingsProviders\CollectionDbProvider;
use Everyware\ProjectPlugin\Components\Contracts\SettingsProvider;
use Infomaker\Everyware\Support\Collection;
use Infomaker\Everyware\Support\Storage\CollectionDB;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CollectionDbProviderSpec extends ObjectBehavior
{
    private $fields = [
        'headline' => 'test',
        'startup_field' => true
    ];

    function let(CollectionDB $db)
    {
        $this->beConstructedWith($db, $this->fields);

        $this->shouldImplement(SettingsProvider::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CollectionDbProvider::class);
    }

    function it_offers_a_way_to_retrieve_stored_settings(CollectionDB $db, Collection $collection)
    {
        $fields = [
            'headline' => 'test',
            'startup_field' => true
        ];

        $db
            ->all()
            ->shouldBeCalled()
            ->willReturn($collection);

        $collection
            ->toArray()
            ->shouldBeCalled()
            ->willReturn($fields);

        $this->get()->shouldReturn($fields);
    }

    function it_offers_a_way_to_retrieve_single_stored_setting(CollectionDB $db, Collection $collection)
    {
        $key = 'headline';
        $result = 'ok';
        $db
            ->all()
            ->shouldBeCalled()
            ->willReturn($collection);

        $collection
            ->pull($key)
            ->shouldBeCalled()
            ->willReturn($result);

        $this->getSingle($key)->shouldReturn($result);
    }

    function it_saves_data_in_the_collectionDB(CollectionDB $db)
    {
        $fields = [
            'new_field' => 'test',
            'test' => true,
            'headline' => 'test_changed',
        ];

        $db
            ->setCollection($fields)
            ->shouldBeCalled()
            ->willReturn($db);

        $db
            ->save()
            ->shouldBeCalled()
            ->willReturn(true);

        $this->save($fields)->shouldReturn(true);
    }

    function it_offers_a_way_to_fetch_the_required_fields()
    {
        $this->requiredFields()->shouldReturn($this->fields);
    }
}
