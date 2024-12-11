<?php declare(strict_types=1);

namespace Everyware\Widget\GoldForex;

use Everyware\ProjectPlugin\Components\WidgetSettingsForm;
use Infomaker\Everyware\Twig\View;

class GoldForexForm extends WidgetSettingsForm
{
  protected $templates = [
    'default' => 'UAE Draft Rate + UAE Gold Rate'
  ];

  /**
   * @param $storedData
   *
   * @return string
   */
  protected function formContent($storedData): string
  {
    return View::generate('@ew-gold-forex/form', array_replace($this->generateViewData($storedData), [
      'form' => $this->generateFormBuilder(),
      'templates' => $this->getTemplates()
    ]));
  }

  private function getTemplates(): array
  {
    return array_map(function ($template, $key) {
      return [
        'value' => $key,
        'text' => $template
      ];
    }, $this->templates, array_keys($this->templates));
  }
}
