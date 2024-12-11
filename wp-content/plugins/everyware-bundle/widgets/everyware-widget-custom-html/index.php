<?php declare(strict_types=1);

/**
 * Plugin Name: Custom html widgets
 * Description: Render custom html
 * Version: 1.0.0
 * Author: Naviga Content Lab AB
 * Author URI: http://www.navigaglobal.com/
 * Text Domain: ew_custom_html
 */

use Everyware\ProjectPlugin\Components\WidgetManager;
use Infomaker\Everyware\Twig\ViewSetup;
use Everyware\ProjectPlugin\ProjectPlugin;
use Everyware\Widget\CustomHtml\AdWidget;
use Everyware\Widget\CustomHtml\CustomHtmlWidget;

if ( ! defined('WPINC') || ! class_exists(ProjectPlugin::class)) {
    exit;
}

ViewSetup::getInstance()->registerTwigFolder('ew-custom-html', __DIR__ . '/src/templates/');

WidgetManager::register(CustomHtmlWidget::class);
WidgetManager::register(AdWidget::class);
