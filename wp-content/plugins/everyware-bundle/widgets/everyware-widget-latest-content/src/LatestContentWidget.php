<?php declare(strict_types=1);

namespace Everyware\Widget\LatestContent;

use Everyware\ProjectPlugin\Components\ComponentSettingsRepository;
use Everyware\ProjectPlugin\Components\Contracts\Admin;
use Everyware\ProjectPlugin\Components\Contracts\InfoManager;
use Everyware\ProjectPlugin\Components\SettingsProviders\SimpleSettingsProvider;
use Everyware\ProjectPlugin\Components\WidgetAdmin;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\Components\Adapters\WidgetAdapter;
use Infomaker\Everyware\Twig\View;

/**
 * Class LatestContentWidget
 */
class LatestContentWidget extends WidgetAdapter
{
  /**
   * @var array
   */
  private $sortOrders = [
    LatestContentApi::SORT_LATEST => 'Latest',
    LatestContentApi::SORT_OLDEST => 'Oldest',
    LatestContentApi::SORT_TITLE_ASC => 'Title A-Z',
    LatestContentApi::SORT_TITLE_DESC => 'Title Z-A'
  ];

  /**
   * @var LatestContent
   */
  private $api;

  /**
   * The setting fields that the widget dependents on
   * @var array
   */
  protected static $fields = [
    'title' => '',
    'content_type' => ''
  ];

  /**
   * @var InfoManager
   */
  protected static $infoManager;

  public static function setInfoManager(InfoManager $infoManager): void
  {
    static::$infoManager = $infoManager;
  }

  public static function registerAjax()
  {
    add_action('wp_ajax_filter_latest_content', static function() {
      $content = LatestContentApi::init();
      $content->renderContentFromRequest($_POST);
    });
  
    add_action('wp_ajax_nopriv_filter_latest_content', static function() {
      $content = LatestContentApi::init();
      $content->renderContentFromRequest($_POST);
    });
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
    return View::generate('@ew-latest-content/widget', array_replace($viewData, [
      'args' => $args,
      'content_type' => $contentType = $viewData['content_type'],
      'sections' => $this->api->getSubSections($contentType),
      'sort' => $this->sortOrders,
      'articles' => $this->api->renderContent([
        'CustomerContentSubType' => $contentType
      ]),
      'settings' => $this->getWidgetSettings()
    ]));
  }

  /**
   * @return Admin
   */
  protected function widgetSetup(): Admin
  {
    $this->api = new LatestContentApi();

    return new WidgetAdmin(
      new LatestContentForm(static::$infoManager ?? new FileReader(__FILE__)),
      new ComponentSettingsRepository(new SimpleSettingsProvider(static::$fields))
    );
  }
}
