<?php declare(strict_types=1);

namespace Everyware\Widget\ArticleList;

use Everyware\Helpers\OcProperties;
use Everyware\ProjectPlugin\Components\ComponentSettingsRepository;
use Everyware\ProjectPlugin\Components\Contracts\Admin;
use Everyware\ProjectPlugin\Components\Contracts\InfoManager;
use Everyware\ProjectPlugin\Components\SettingsProviders\SimpleSettingsProvider;
use Everyware\ProjectPlugin\Components\WidgetAdmin;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Infomaker\Everyware\Support\Str;
use Everyware\ProjectPlugin\Components\Adapters\WidgetAdapter;
use Infomaker\Everyware\Twig\View;
use OcAPI;
use OcArticle;

/**
 * Class ArticleListWidget
 */
class ArticleListWidget extends WidgetAdapter
{

    /**
     * The setting fields that the widget dependents on
     * @var array
     */
    protected static $fields = [
        'title' => '',
        'page' => '',
        'template' => '',
        'render_as_list' => false,
        'oc_query' => '',
        'oc_query_start' => 1,
        'oc_query_limit' => 10,
        'oc_query_sort' => '',
    ];

    /**
     * @var OcAPI
     */
    private $ocApi;

    /**
     * @var string
     */
    private $defaultTemplate = 'ocarticle-default.php';

    /**
     * @var InfoManager
     */
    protected static $infoManager;

    /**
     * @var OcProperties
     */
    protected static $defaultProperties;

    /**
     * Convert the form-data into values readable by OpenContent::search()
     *
     * @param $viewData
     *
     * @return array
     */
    protected function convertOcParams($viewData): array
    {
        return [
            'contenttypes' => ['Article'],
            'q' => $viewData['oc_query'],
            // Subtract oc_query_start with 1 since we do not start on 0 in admin form.
            'sort.name' => $viewData['oc_query_sort'],
            'limit' => $viewData['oc_query_limit'],
            'start' => max(0, (int)$viewData['oc_query_start'] - 1),
        ];
    }

    protected function doOcSearch(array $params, $createArticle = true): array
    {
        $result = $this->ocApi->search($params, true, $createArticle);

        unset($result['facet'], $result['hits'], $result['duration']);

        return $result;
    }

    protected function fetchWithRelations($params): array
    {
        $params['properties'] = $this->getProperties()->all();

        return $this->doOcSearch($params);
    }

    protected function fetchWithoutRelations($params): array
    {
        $params['properties'] = ['uuid'];

        $list = $this->doOcSearch($params, false);

        $articles = [];

        foreach ($list as $article) {
            if ($article instanceof OcArticle) {
                $articles[] = $this->ocApi->get_single_object($article->get_value('uuid'));
            }
        }

        return $articles;
    }

    protected function fetchArticles(array $params): array
    {
        if ($this->usePropertyRelationSearch()) {
            return $this->fetchWithRelations($params);
        }

        return $this->fetchWithoutRelations($params);
    }

    protected function generateArticleList($viewData): array
    {
        $articles = $this->fetchArticles($this->convertOcParams($viewData));

        return $this->generateFromTemplate($articles, $viewData['template']);
    }

    /**
     * Run the article through the template to generate HTML
     *
     * @param OcArticle[] $ocArticles
     * @param string      $template
     *
     * @return array
     */
    private function generateFromTemplate(array $ocArticles, $template = ''): array
    {
        $templateFile = Str::notEmpty($template) ? $template : $this->defaultTemplate;
        $template = locate_template([$templateFile, "/templates/{$templateFile}"]);

        $articles = [];
        ob_start();
        foreach ($ocArticles as $article) {
            $templateFile = $template;
            include $templateFile;
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
        return View::generate('@ew-article-list/widget', array_replace($viewData, [
            'args' => $args,
            'articles' => $this->generateArticleList($viewData),
            'settings' => $this->getWidgetSettings()
        ]));
    }

    protected function getProperties(): OcProperties
    {
        if ( ! static::$defaultProperties) {
            static::$defaultProperties = new OcProperties(apply_filters('ew_apply_default_search_properties', []));
        }

        return static::$defaultProperties;
    }

    protected function usePropertyRelationSearch(): bool
    {
        return $this->getProperties()->hasRelations();
    }

    protected function widgetSetup(): Admin
    {
        $this->ocApi = new OcAPI();

        return new WidgetAdmin(
            new ArticleListForm(static::$infoManager ?? new FileReader(__FILE__), $this->ocApi),
            new ComponentSettingsRepository(new SimpleSettingsProvider(static::$fields))
        );
    }

    public static function setInfoManager(InfoManager $infoManager): void
    {
        static::$infoManager = $infoManager;
    }
}
