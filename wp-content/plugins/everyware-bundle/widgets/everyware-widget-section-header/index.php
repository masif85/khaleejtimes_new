<?php

/**
 * Widget widget.
 * Plugin Name: Section Header
 * Description: Display a header in sections
 * Version: 0.2.0
 * Text Domain: ew_section_header
 * Author: Infomaker Scandinavia AB
 * Author URI: http://infomaker.se
 */

use Everyware\ProjectPlugin\Components\WidgetManager;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\TwigSetup;
use Everyware\Widget\SectionHeader\SectionHeaderWidget;


TwigSetup::registerTwigFolder('section-header', __DIR__ . '/src/templates');
SectionHeaderWidget::setInfoManager(new FileReader(__FILE__));

WidgetManager::register(SectionHeaderWidget::class);
