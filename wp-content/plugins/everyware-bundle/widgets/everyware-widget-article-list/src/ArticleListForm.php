<?php declare(strict_types=1);

namespace Everyware\Widget\ArticleList;

use Everyware\ProjectPlugin\Components\Contracts\InfoManager;
use Everyware\ProjectPlugin\Components\WidgetSettingsForm;
use Infomaker\Everyware\Base\Pages;
use Infomaker\Everyware\Base\Templates;
use Infomaker\Everyware\Twig\View;
use OcAPI;

class ArticleListForm extends WidgetSettingsForm
{
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
        if ( ! isset($storedData['oc_query_start'])) {
            $storedData['oc_query_start'] = 1;
        }

        $storedData['oc_query_start'] = $this->validateStartFromValue($storedData['oc_query_start']);

        return View::generate('@ew-article-list/form', array_replace($this->generateViewData($storedData), [
            'pages' => Pages::getSelectData(),
            'templates' => Templates::getSelectData(),
            'sortings' => $this->sortingSelectData($storedData['oc_query_sort'] ?? ''),
            'settings' => $this->getFormSettings(),
            'form' => $this->generateFormBuilder()
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

    private function validateStartFromValue($value): int
    {
        if ( ! is_int($value)) {
            $value = (int)$value;
        }

        // Verify that oc query start is at least 1 in admin
        return max($value, 1);
    }
}
