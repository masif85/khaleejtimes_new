<?php declare(strict_types=1);

/**
 * Plugin Name: Gold/forex
 * Description: Render gold/forex tables from articles
 * Version: 1.0.0
 * Author: Naviga Web Team
 * Author URI: https://navigaglobal.com/web
 * Text Domain: ew_gold_forex
 */

use Everyware\ProjectPlugin\Components\WidgetManager;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\TwigSetup;
use Everyware\Widget\GoldForex\GoldForexWidget;

TwigSetup::registerTwigFolder('ew-gold-forex', __DIR__ . '/src/templates');

GoldForexWidget::setInfoManager(new FileReader(__FILE__));

WidgetManager::register(GoldForexWidget::class);
