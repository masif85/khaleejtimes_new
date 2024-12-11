<?php declare(strict_types=1);

namespace Everyware\Widget\LatestContent;

use Everyware\ProjectPlugin\Components\WidgetSettingsForm;
use Infomaker\Everyware\Twig\View;

class LatestContentForm extends WidgetSettingsForm
{
  /**
   * @var array
   */
  private $contentTypes = ['video', 'gallery', 'podcast'];

  /**
   * @param $storedData
   *
   * @return string
   */
  protected function formContent($storedData): string
  {
    return View::generate('@ew-latest-content/form', array_replace($this->generateViewData($storedData), [
      'types' => $this->getContentTypes(),
      'form' => $this->generateFormBuilder()
    ]));
  }

  /**
   * ContentSubTypes for dropdown
   *
   * @return array
   */
  private function getContentTypes(): array
  {
    return array_map(static function ($type) {
      return [
        'value' => strtoupper($type),
        'text' => $type
      ];
    }, $this->contentTypes);
  }
}
