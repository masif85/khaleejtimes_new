<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components;

/**
 * Class WidgetSettingsForm
 * @package Everyware\ProjectPlugin\Components
 */
abstract class WidgetSettingsForm extends ComponentSettingsForm
{
    /**
     * @param array $storedData
     *
     * @return array
     */
    protected function generateViewData(array $storedData): array
    {
        $settingsHandler =  new SettingsHandler('widget-' . $this->getFormPrefix());

        return $settingsHandler->generateGroupedFormData((string)$storedData['instance_id'], $storedData);
    }
}
