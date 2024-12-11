<?php declare(strict_types=1);

namespace Everyware\Widget\ArticleList2;

use Everyware\ProjectPlugin\Components\ComponentSettingsRepository;
use Everyware\ProjectPlugin\Components\Contracts\Admin;
use Everyware\ProjectPlugin\Components\SettingsProviders\SimpleSettingsProvider;
use Everyware\ProjectPlugin\Components\WidgetAdmin;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Support\Str;
use Everyware\ProjectPlugin\Components\Adapters\WidgetAdapter;
use Everyware\ProjectPlugin\Components\Contracts\InfoManager;
use OcAPI;
use Everyware\Helpers\OcProperties;

/**
 * Class ConceptArticlesWidget
 */
class ArticleListWidget extends WidgetAdapter
{
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
   * The setting fields that the widget dependents on
   * @var array
   */
  protected static $fields = [
    'title' => '',
    'first_article_template' => '',
    'template' => '',
    'class' => '',
    'oc_query' => '',
    'oc_query_start' => 1,
    'oc_query_limit' => 3,
    'oc_query_sort' => ''
  ];

  /**
   * Render widget content
   *
   * @param array $viewData
   * @param array $args
   * @return string
   */
  protected function generateWidget(array $viewData, array $args): string
  {
    return View::generate('@ew-article-list2/widget', array_replace($viewData, [
      'args' => $args,
      'articles' => $this->generateArticleList($viewData),
      'settings' => $this->getWidgetSettings()
    ]));
  }

  private function fetchArticles(array $viewData): array
  {
    $result = $this->ocApi->search([
      'contenttypes' => ['Article'],
      'properties' => $this->getProperties()->all(),
      'q' => $viewData['oc_query'],
      // Subtract oc_query_start with 1 since we do not start on 0 in admin form.
        'sort.name' => $viewData['oc_query_sort'],
      'limit' => $viewData['oc_query_limit'],
      'start' => max(0, (int)$viewData['oc_query_start'] - 1),
    ], true);

    unset($result['facet'], $result['hits'], $result['duration']);

    return $result;
  }
  
  private function getProperties(): OcProperties
  {
    if (!static::$defaultProperties) {
      static::$defaultProperties = new OcProperties(apply_filters('ew_apply_default_search_properties', []));
    }

    return static::$defaultProperties;
  }

  private function generateArticleList($viewData): array
  {
    $articles = $this->fetchArticles($viewData);

    return $this->generateFromTemplate($articles, $viewData);
  }

  /**
   * Run the article through the template to generate HTML
   *
   * @param OcArticle[] $ocArticles
   * @param array      $viewData
   *
   * @return array
   */
  private function generateFromTemplate(array $ocArticles, array $viewData): array
  {
    $firstTemplate = $this->getTemplate($viewData['first_article_template'] ?? '');
    $template = $this->getTemplate($viewData['template'] ?? '');
    
    $articles = [];
    ob_start();
    foreach ($ocArticles as $key => $article) {
        $templateFile = $key ? $template : $firstTemplate;
        include $templateFile;
        $articles[] = ob_get_contents();
        ob_clean();
    }
    ob_end_clean();

    return $articles;
  }

  private function getTemplate(string $template): string
  {
    $templateFile = Str::notEmpty($template) ? $template : $this->defaultTemplate;
    
    return locate_template([$templateFile, "/templates/{$templateFile}"]);
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
