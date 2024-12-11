<?php declare(strict_types=1);

/*
 * Plugin Name: Most Read - Google Analytics
 * Description: A list of articles fetched from Google Analytics. Has option to add headline, link to section and provide custom view id.
 * Version: 1.0.0
 * Author: Infomaker Scandinavia AB
 * Author URI: https://infomaker.se
 */

namespace Everyware\Plugin\GoogleAnalytics;

use Everyware\ProjectPlugin\Components\Adapters\WidgetAdapter;
use Everyware\ProjectPlugin\Components\ComponentSettingsRepository;
use Everyware\ProjectPlugin\Components\Contracts\Admin;
use Everyware\ProjectPlugin\Components\Contracts\InfoManager;
use Everyware\ProjectPlugin\Components\SettingsProviders\SimpleSettingsProvider;
use Everyware\ProjectPlugin\Components\WidgetAdmin;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Google\Analytics\Data\V1beta\Filter\StringFilter\MatchType;
use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Base\Templates;
use OcArticle;

class MostReadWidget extends WidgetAdapter
{
    /**
     * @var array
     */
    protected static $fields = [
        'title' => '',
        'limit' => 1,
        'render_as_list' => false,
        'match_type' => MatchType::PARTIAL_REGEXP,
        'filter_value' => '\/(?:[0-9a-zA-Z-])*\/.+',
        'case_sensitive' => false,
        'page' => '',
        'start' => 1,
        'template' => '',
    ];

    /**
     * @var InfoManager
     */
    protected static $infoManager;

    /**
     * @var GoogleAnalyticsClient
     */
    protected static $client;

    /**
     * Twig-template to use
     *
     * @var string
     */
    protected $templatePath = '@plugins/widgets/most-read';

    protected function widgetSetup(): Admin
    {
        return new WidgetAdmin(
            new MostReadForm(static::$infoManager ?? new FileReader(__FILE__), self::$client),
            new ComponentSettingsRepository(new SimpleSettingsProvider(static::$fields))
        );
    }

    public static function setInfoManager(InfoManager $infoManager): void
    {
        static::$infoManager = $infoManager;
    }

    public static function setClient(GoogleAnalyticsClient $client): void
    {
        static::$client = $client;
    }

    /**
     * Run the article through the template to generate HTML
     *
     * @param OcArticle[] $ocArticles
     * @param string      $template
     *
     * @return array
     */
    private function generateFromTemplate($ocArticles, $template = ''): array
    {
        $articles = [];
        ob_start();
        foreach ($ocArticles as $index => $article) {
            include $template;
            $articles[] = ob_get_contents();
            ob_clean();
        }
        ob_end_clean();

        return $articles;
    }

    /**
     * @param array $viewData
     * @param array $args
     *
     * @return string
     */
    protected function generateWidget(array $viewData, array $args): string
    {
        static::$client->customize([
            'match_type' => $viewData['match_type'],
            'value' => $viewData['filter_value'],
            'case_sensitive' => $viewData['case_sensitive'],
        ]);

        // Get real article data back from WP and OC
        $articles = static::$client->getArticlesFromPosition($viewData['start'], $viewData['limit']);
        $template = $this->shouldRenderAsList($viewData) ? 'article-list' : 'articles';

        return View::generate("{$this->templatePath}/{$template}", array_replace($viewData, [
            'articles' => $this->generateFromTemplate($articles, Templates::getPath($viewData['template'])),
            'args' => $args,
            'settings' => $this->getWidgetSettings()
        ]));
    }

    /**
     * Determine if the widget should be rendered as a list
     * @param array $viewData
     *
     * @return bool
     */
    private function shouldRenderAsList(array $viewData): bool
    {
        return filter_var($viewData['render_as_list'], FILTER_VALIDATE_BOOLEAN);
    }
}
