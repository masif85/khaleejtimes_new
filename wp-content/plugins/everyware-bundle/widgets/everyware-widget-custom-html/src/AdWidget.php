<?php declare(strict_types=1);

/*
 * Plugin Name: Ad - custom html
 * Description: Custom html widget for ads
 * Version: 1.0.0
 * Author: Infomaker Scandinavia AB
 * Author URI: https://infomaker.se
 */

namespace Everyware\Widget\CustomHtml;

use Everyware\ProjectPlugin\Components\Adapters\WidgetAdapter;
use Everyware\ProjectPlugin\Components\ComponentSettingsRepository;
use Everyware\ProjectPlugin\Components\Contracts\Admin;
use Everyware\ProjectPlugin\Components\SettingsProviders\SimpleSettingsProvider;
use Everyware\ProjectPlugin\Components\WidgetAdmin;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Infomaker\Everyware\Twig\View;

/**
 * Class AdWidget
 */
class AdWidget extends WidgetAdapter
{
  /**
   * @var array
   */
  protected static $fields = [
    'content' => ''
  ];

  protected function generateWidget(array $viewData, array $args): string
  {
    return View::generate('@ew-custom-html/widget', $viewData);
  }

  /**
   * @return Admin
   */
  protected function widgetSetup(): Admin
  {
    return new WidgetAdmin(
      new CustomHtmlForm(new FileReader(__FILE__)),
      new ComponentSettingsRepository(new SimpleSettingsProvider(static::$fields))
    );
  }
}
