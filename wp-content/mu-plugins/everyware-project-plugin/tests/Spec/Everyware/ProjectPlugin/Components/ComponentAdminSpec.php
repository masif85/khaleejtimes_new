<?php /** @noinspection ALL */

namespace Spec\Everyware\ProjectPlugin\Components;

use Everyware\ProjectPlugin\Components\ComponentAdmin;
use Everyware\ProjectPlugin\Components\Contracts\SettingsRepository;
use Everyware\ProjectPlugin\Components\Contracts\SettingsForm;
use PhpSpec\ObjectBehavior;

class ComponentAdminSpec extends ObjectBehavior
{
    private $currentVersion = '1.0.0';

    function let(
        SettingsForm $form,
        SettingsRepository $settings
    ) {
        $form
            ->currentVersion()
            ->shouldBeCalledOnce()
            ->willReturn($this->currentVersion);

        $settings->addRequiredField(ComponentAdmin::VERSION_FIELD, $this->currentVersion)->shouldBeCalled();

        $this->beConstructedWith($form, $settings);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComponentAdmin::class);
    }

    function it_can_present_current_component_version(SettingsForm $form)
    {
        $this->currentVersion()->shouldReturn($this->currentVersion);
    }

    function it_can_present_component_description(SettingsForm $form)
    {
        $result = 'ok';
        $form
            ->getDescription()
            ->shouldBeCalled()
            ->willReturn($result);
        $this->getDescription()->shouldReturn($result);
    }

    function it_can_present_component_name(SettingsForm $form)
    {
        $result = 'ok';
        $form
            ->getName()
            ->shouldBeCalled()
            ->willReturn($result);

        $this->getName()->shouldReturn($result);
    }

    function it_can_fetch_specific_component_information(SettingsForm $form)
    {
        $componentInfoMap = [
            'name' => 'Plugin Name',
            'key' => 'value'
        ];

        $form
            ->getComponentInfo($componentInfoMap)
            ->shouldBeCalled()
            ->willReturn([]);

        $this->getComponentInfo($componentInfoMap)->shouldReturn([]);
    }

    function it_uses_SettingsForm_to_get_inputPrefix(SettingsForm $form)
    {
        $prefix = 'ok';
        $form->getFormPrefix()
            ->shouldBeCalled()
            ->willReturn($prefix);

        $this->getInputPrefix()->shouldReturn($prefix);
    }

    function it_uses_SettingsForm_to_get_id(SettingsForm $form)
    {
        $prefix = 'ok';
        $form->getName()
            ->shouldBeCalled()
            ->willReturn($prefix);

        $this->getId()->shouldReturn($prefix);
    }

    function it_forwards_to_SettingsForm_create_on_creation(SettingsForm $form)
    {
        $result = 'ok';
        $storedData = [];
        $form
            ->create($storedData)
            ->shouldBeCalledOnce()
            ->willReturn($result);

        $this->create($storedData)->shouldReturn($result);
    }

    function it_forwards_to_SettingsForm_edit(SettingsForm $form)
    {
        $result = 'ok';
        $storedData = [];
        $form
            ->edit($storedData)
            ->shouldBeCalledOnce()
            ->willReturn($result);

        $this->edit($storedData)->shouldReturn($result);
    }

    function it_forwards_to_SettingsRepository_store(SettingsRepository $settings)
    {
        $result = true;
        $newData = ['data_to_store'];
        $storedData = [];
        $settings
            ->store($newData)
            ->shouldBeCalledOnce()
            ->willReturn($result);

        $this->store($newData, $storedData)->shouldReturn($result);
    }

    function it_forwards_to_SettingsRepository_update(SettingsRepository $settings)
    {
        $result = true;
        $newData = ['data_to_update'];
        $storedData = ['stored_data'];
        $settings
            ->update($newData, $storedData)
            ->shouldBeCalledOnce()
            ->willReturn($result);

        $this->update($newData, $storedData)->shouldReturn($result);
    }
}
