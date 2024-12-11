<?php declare(strict_types=1);

namespace Everyware\Widget\Lists;

use Everyware\ProjectPlugin\Components\Contracts\InfoManager;
use Everyware\ProjectPlugin\Components\WidgetSettingsForm;
use Infomaker\Everyware\Twig\View;
use OcAPI;

class ListForm extends WidgetSettingsForm
{
  protected $templates = ['video'];

  /**
   * @var OcAPI
   */
  private $ocApi;

  public function __construct(InfoManager $infoManager, OcAPI $ocApi)
  {
    $this->ocApi = $ocApi;
    parent::__construct($infoManager);
  }

  /**
   * @param $storedData
   *
   * @return string
   */
  protected function formContent($storedData): string
  {
    return View::generate('@ew-lists/form', array_replace($this->generateViewData($storedData), [
      'form' => $this->generateFormBuilder(),
      'templates' => $this->getTemplates(),
      'lists' => $this->getLists()
    ]));
  }

  private function getTemplates(): array
  {
    return array_map(function ($template) {
      return [
        'value' => $template,
        'text' => ucfirst($template)
      ];
    }, $this->templates);
  }

  private function getLists()
  {
    $this->ocApi->prepare_suggest_query([
      'oc_suggest_field' => 'Name',
      'oc_query' => 'contenttype:List'
    ]);

    $result = $this->ocApi->get_oc_suggest();
 
    return array_map(static function ($list) {
      return [
        'value' => $list->name,
        'text' => $list->name 
      ];
    },  $result);
  }
}
