<?php declare(strict_types=1);

namespace Everyware\Widget\Lists;

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Everyware\ProjectPlugin\Components\ComponentSettingsRepository;
use Everyware\ProjectPlugin\Components\Contracts\Admin;
use Everyware\ProjectPlugin\Components\Contracts\InfoManager;
use Everyware\ProjectPlugin\Components\SettingsProviders\SimpleSettingsProvider;
use Everyware\ProjectPlugin\Components\WidgetAdmin;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\Components\Adapters\WidgetAdapter;
use Infomaker\Everyware\Twig\View;
use OcAPI;
use KTDTheme\ViewModels\Teasers\Teaser;
use KTDTheme\ViewModels\Teasers\VideoTeaser;

/**
 * Class ListWidget
 */
class ListWidget extends WidgetAdapter
{
  /**
   * The setting fields that the widget dependents on
   * @var array
   */
  protected static $fields = [
    'title' => '',
    'template' => 'video',
    'list' => ''
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
    return View::generate('@ew-lists/' . $this->getTemplate($viewData), array_replace($viewData, [
      'args' => $args,
      'articles' => $this->getArticles($viewData),
      'settings' => $this->getWidgetSettings()
    ]));
  }

  /**
   * @return Admin
   */
  protected function widgetSetup(): Admin
  {
    return new WidgetAdmin(
      new ListForm(static::$infoManager ?? new FileReader(__FILE__), new OcAPI()),
      new ComponentSettingsRepository(new SimpleSettingsProvider(static::$fields))
    );
  }

  private function getArticles(array $viewData): ?array
  {
    $provider = OpenContentProvider::setup( [
      'q' => sprintf('Name:"%s"', $viewData['list'] ?? '*'),
      'contenttypes' => ['List'],
    ] );
  
    $provider->setPropertyMap('ListArticles');
    
    $lists = $provider->queryWithRequirements();

    if (!$lists || !$list = $lists[0]) {
      return null;
    }

    return $list->articles->map(function(NewsMLArticle $article) use ($viewData) {
      return $this->getTeaser($article, $this->getTemplate($viewData))->getViewData();
    })->toArray();
  }

  private function getTeaser(NewsMLArticle $article, string $template): Teaser
  {
    switch ($template) {
      case 'video':
        return new VideoTeaser($article);
      default:
        return new Teaser($article);
    }
  }

  private function getTemplate(array $viewData): string
  {
    return $viewData['template'] ?? self::$fields['template'];
  }
}
