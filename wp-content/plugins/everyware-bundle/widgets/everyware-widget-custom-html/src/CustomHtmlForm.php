<?php declare(strict_types=1);

namespace Everyware\Widget\CustomHtml;

use Everyware\ProjectPlugin\Components\WidgetSettingsForm;
use Infomaker\Everyware\Twig\View;

class CustomHtmlForm extends WidgetSettingsForm
{
  /**
   * @param $storedData
   *
   * @return string
   */
  protected function formContent($storedData): string
  {
    $storedData['title'] = $storedData['board_widget_name'] ?? '';
    
    return View::generate('@ew-custom-html/form', array_replace($this->generateViewData($storedData), [
      'form' => $this->generateFormBuilder()
    ]));
  }
}