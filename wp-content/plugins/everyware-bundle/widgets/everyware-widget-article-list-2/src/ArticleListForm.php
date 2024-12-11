<?php declare(strict_types=1);

namespace Everyware\Widget\ArticleList2;

use Infomaker\Everyware\Base\Templates;
use Infomaker\Everyware\Twig\View;
use Everyware\ProjectPlugin\Components\WidgetSettingsForm;
use Everyware\ProjectPlugin\Components\Contracts\InfoManager;
use OcAPI;

class ArticleListForm extends WidgetSettingsForm
{
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
    return View::generate('@ew-article-list2/form', array_replace($this->generateViewData($storedData), [
      'templates' => Templates::getSelectData(),
      'form' => $this->generateFormBuilder(),
      'sortings' => $this->sortingSelectData($storedData['oc_query_sort'] ?? '')
    ]));
  }
    /**
     * Function to get available sort options
     *
     * @param string $savedSortOption
     *
     * @return array
     */
    protected function sortingSelectData(string $savedSortOption): array
    {
        $sortOptions = $this->ocApi->get_oc_sort_options();
        if ( ! isset($sortOptions->sortings)) {
            return [];
        }
        $savedOptionIsPresent = false;

        $preparedSortData = array_map(static function ($option) use ($savedSortOption, &$savedOptionIsPresent) {
            if ($option->name === $savedSortOption) {
                $savedOptionIsPresent = true;
            }

            return [
                'value' => $option->name,
                'text' => $option->name
            ];
        }, $sortOptions->sortings);

        if ( ! $savedOptionIsPresent) {
            array_unshift($preparedSortData, [
                'value' => '',
                'text' => ''
            ]);
        }

        return $preparedSortData;
    }
}
