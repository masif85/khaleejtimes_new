<?php declare(strict_types=1);

/**
 * Plugin Name: Article list 2
 * Description: List of articles fetched from Open Content. You can specify different teaser for first article in the list.
 * Version: 1.0.0
 * Author: Naviga Web Team
 * Author URI: https://navigaglobal.com/web
 * Text Domain: ew_articles_list_2
 */

use Everyware\ProjectPlugin\Components\WidgetManager;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\TwigSetup;
use Everyware\Widget\ArticleList2\ArticleListWidget;


TwigSetup::registerTwigFolder('ew-article-list2', __DIR__ . '/src/templates');

ArticleListWidget::setInfoManager(new FileReader(__FILE__));

WidgetManager::register(ArticleListWidget::class);
