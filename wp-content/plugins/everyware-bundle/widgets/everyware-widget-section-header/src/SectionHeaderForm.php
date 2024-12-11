<?php declare(strict_types=1);

namespace Everyware\Widget\SectionHeader;

use Infomaker\Everyware\Twig\View;
use Everyware\ProjectPlugin\Components\WidgetSettingsForm;

class SectionHeaderForm extends WidgetSettingsForm
{
    protected function formContent($storedData): string
    {
        return View::generate('@section-header/form', $this->transformData($storedData));
    }

    protected function transformData($storedData)
    {
        return array_replace($this->generateViewData($storedData), [
            'form' => $this->generateFormBuilder(),
        ]);
    }
}