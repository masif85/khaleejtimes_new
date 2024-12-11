<?php declare(strict_types=1);

/**
 * Plugin Name: Latest Content
 * Description: Show latest videos, galleries or podcasts fetched from Open Content.
 * Version: 1.0.0
 * Author: Naviga Web Team
 * Author URI: https://navigaglobal.com/web
 * Text Domain: ew_latest_content
 */

use Everyware\ProjectPlugin\Components\WidgetManager;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\TwigSetup;
use Everyware\Widget\LatestContent\LatestContentWidget;
use Everyware\Widget\LatestContent\LatestContentApi;

TwigSetup::registerTwigFolder('ew-latest-content', __DIR__ . '/src/templates');

LatestContentWidget::setInfoManager(new FileReader(__FILE__));
LatestContentWidget::registerAjax();

WidgetManager::register(LatestContentWidget::class);
