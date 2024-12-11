<?php declare(strict_types=1);

/**
 * Plugin Name: List
 * Description: Render content from OC lists
 * Version: 1.0.0
 * Author: Naviga Web Team
 * Author URI: https://navigaglobal.com/web
 * Text Domain: ew_lists
 */

use Everyware\ProjectPlugin\Components\WidgetManager;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\TwigSetup;
use Everyware\Widget\Lists\ListWidget;

TwigSetup::registerTwigFolder('ew-lists', __DIR__ . '/src/templates');

ListWidget::setInfoManager(new FileReader(__FILE__));

WidgetManager::register(ListWidget::class);
