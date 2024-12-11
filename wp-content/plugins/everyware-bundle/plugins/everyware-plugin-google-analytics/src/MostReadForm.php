<?php declare(strict_types=1);

namespace Everyware\Plugin\GoogleAnalytics;

use Everyware\ProjectPlugin\Components\Contracts\InfoManager;
use Everyware\ProjectPlugin\Components\WidgetSettingsForm;
use Infomaker\Everyware\Base\Pages;
use Infomaker\Everyware\Base\Templates;
use Infomaker\Everyware\Twig\View;

/**
 * Class MostReadForm
 * @package Everyware\Plugin\GoogleAnalytics
 */
class MostReadForm extends WidgetSettingsForm
{
    private $templatePath = '@plugins/widgets/most-read';

    /** @var GoogleAnalyticsClient */
    private $client;

    public function __construct(InfoManager $infoManager, GoogleAnalyticsClient $client)
    {
        $this->client = $client;

        parent::__construct($infoManager);
    }

    protected function formContent($storedData): string
    {
        $viewData = \array_replace($this->generateViewData($storedData), [
            'pages' => Pages::getSelectData(),
            'templates' => Templates::getSelectData(),
            'settings' => $this->getFormSettings(),
            'form' => $this->generateFormBuilder(),
            'match_types' => $this->client->getMatchTypes(),
        ]);

        return View::generate($this->templatePath . '/form', $viewData);
    }
}
