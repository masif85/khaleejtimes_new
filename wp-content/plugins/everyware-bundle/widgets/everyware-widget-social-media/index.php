<?php declare(strict_types=1);

/**
 * Widget widget.
 * Plugin Name: Social media icons
 * Description: Display social media icons
 * Version: 0.2.0
 * Text Domain: ew_social_media_icons
 * Author: Infomaker Scandinavia AB
 * Author URI: http://infomaker.se
 */

use Everyware\ProjectPlugin\Components\WidgetManager;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\TwigSetup;
use Everyware\Widget\SocialMedia\SocialMediaWidget;

TwigSetup::registerTwigFolder('social_media_widget', __DIR__ . '/src/templates');

SocialMediaWidget::setInfoManager(new FileReader(__FILE__));

WidgetManager::register(SocialMediaWidget::class);
