<?php declare(strict_types=1);

namespace Everyware\Widget\GoldForex;

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Everyware\ProjectPlugin\Components\ComponentSettingsRepository;
use Everyware\ProjectPlugin\Components\Contracts\Admin;
use Everyware\ProjectPlugin\Components\Contracts\InfoManager;
use Everyware\ProjectPlugin\Components\SettingsProviders\SimpleSettingsProvider;
use Everyware\ProjectPlugin\Components\WidgetAdmin;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\Components\Adapters\WidgetAdapter;
use Infomaker\Everyware\Support\Collection;
use Infomaker\Everyware\Twig\View;
use DOMDocument;

/**
 * Class GoldForexWidget
 */
class GoldForexWidget extends WidgetAdapter
{
  private $templateArticles = [
    'default' => [
      'b79e4450-81f2-46ec-8752-6c2ff8e37890' => 'UAE Draft Rate',
      'a2344da1-7204-4131-9f2c-1b94a6688cbd' => 'UAE Gold Rate'
    ]
  ];

  /**
   * The setting fields that the widget dependents on
   * @var array
   */
  protected static $fields = [
    'title' => '',
    'template' => 'default'
  ];

  /**
   * @var InfoManager
   */
  protected static $infoManager;

  public static function setInfoManager(InfoManager $infoManager): void
  {
    static::$infoManager = $infoManager;
  }

  /**
   * Render widget content
   *
   * @param array $viewData
   * @param array $args
   * @return string
   */
  protected function generateWidget(array $viewData, array $args): string
  {
    $template = $viewData['template'] ?? 'default';

    return View::generate('@ew-gold-forex/widget', array_replace($viewData, [
      'args' => $args,
      'template' => $template,
      'page_link' => $this->getPageLink(),
      'tables' => $this->getTables($template),
      'settings' => $this->getWidgetSettings()
    ]));
  }

  /**
   * @return Admin
   */
  protected function widgetSetup(): Admin
  {
    return new WidgetAdmin(
      new GoldForexForm(static::$infoManager ?? new FileReader(__FILE__)),
      new ComponentSettingsRepository(new SimpleSettingsProvider(static::$fields))
    );
  }

  private function getTables(string $template): ?array
  {
    $provider = OpenContentProvider::setup( [
      'q' => 'CustomerContentSubType:GOLDFOREX',
      'contenttypes' => ['Article'],
    ] );
  
    $provider->setPropertyMap('Article');
    
    $articles = new Collection($provider->queryWithRequirements());

    $articles = $articles->keyBy('uuid');
    
    $tables = new Collection($this->templateArticles[$template] ?? []);
    
    return $tables->map(function($title, $uuid) use ($articles) {
      $article = $articles->get($uuid);
      return [
        'title' => $title,
        'rows' => $article ? $this->getTableRows($article) : []
      ];
    })->toArray();
  }

  private function getTableRows(NewsMLArticle $article)
  {
    $articleBody = $article->getParsedBody();
    $table = $articleBody->firstOfType('x-im/table');
    $rows = [];
    
    if ($table) {
      $dom = new DOMDocument;
      $dom->loadHTML($table->getItemAttribute('body'));

      foreach ($dom->getElementsByTagName('tr') as $tr) {
        if (count($rows) >= 2) {
          break;
        }
        $data = [];
        foreach ($tr->childNodes as $td) {
          $data[] = $td->nodeValue;
        }
        $rows[] = $data;
      }
    }
    
    return $rows;
  }

  private function getPageLink(): string
  {
    $pages = get_posts([
      'meta_key' => '_wp_page_template',
      'meta_value' => 'page-gold-forex.php',
      'post_parent' => 0,
      'post_type' => 'page',
    ]);
    
    $page = $pages[0] ?? null;

    return $page ? get_permalink($page) : '';
  }
}
