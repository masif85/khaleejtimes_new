<?php declare(strict_types=1);

namespace Everyware\Plugin\RedirectOriginalUrls;

use Everyware\ProjectPlugin\Components\ComponentSettingsForm;
use Infomaker\Everyware\Twig\View;

/**
 * Class SettingsForm
 * @package Everyware\Plugin\RedirectOriginalUrls
 */
class PluginSettingsForm extends ComponentSettingsForm
{
    /**
     * @param array $storedData
     *
     * @return string
     */
    protected function formContent($storedData): string
    {
        $viewData = array_replace($this->generateViewData($storedData), [
            'settings' => $this->getFormSettings(),
            'form' => $this->generateFormBuilder()
        ]);
        return View::generate('@plugin-redirect-original-urls/plugin-settings', $viewData);
    }
}

