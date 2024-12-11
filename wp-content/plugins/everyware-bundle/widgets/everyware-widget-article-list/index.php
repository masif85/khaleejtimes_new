<?php declare(strict_types=1);

/**
 * Plugin Name: Article List
 * Description: List of articles fetched from Open Content. Can be linked to a "Page".
 * Version: 0.5.0
 * Author: Naviga Web Team
 * Author URI: https://navigaglobal.com/web
 * Text Domain: ew_article_list
 */

use Everyware\ProjectPlugin\Components\WidgetManager;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\TwigSetup;
use Everyware\Widget\ArticleList\ArticleListWidget;

TwigSetup::registerTwigFolder('ew-article-list', __DIR__ . '/src/templates');

ArticleListWidget::setInfoManager(new FileReader(__FILE__));

WidgetManager::register(ArticleListWidget::class);
