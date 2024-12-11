<?php

/**
 * Plugin Name: Google Analytics Plugin
 * Description: Import credentials and setup your site with Google Analytics
 * Version: 1.0.0
 * Author: Infomaker Scandinavia AB
 * Author URI: https://infomaker.se
 */

use Everyware\Plugin\GoogleAnalytics\GoogleAnalyticsClient;
use Everyware\Plugin\GoogleAnalytics\MostReadWidget;
use Everyware\Plugin\GoogleAnalytics\PluginSettings;
use Everyware\Plugin\GoogleAnalytics\PluginSettingsAdmin;
use Everyware\Plugin\GoogleAnalytics\PluginSettingsForm;
use Everyware\Plugin\GoogleAnalytics\GoogleTracker;
use Everyware\ProjectPlugin\Components\Adapters\PluginSettingsTabAdapter;
use Everyware\ProjectPlugin\Components\WidgetManager;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\Helpers\JsonImporter;
use Everyware\ProjectPlugin\ProjectPlugin;
use Infomaker\Everyware\Twig\ViewSetup;

if ( ! defined('WPINC') || ! class_exists(ProjectPlugin::class)) {
    exit;
}

ViewSetup::getInstance()->registerTwigFolder('plugins', __DIR__ . '/src/templates/');

$settings = PluginSettings::create();

MostReadWidget::setClient(new GoogleAnalyticsClient($settings));
WidgetManager::register(MostReadWidget::class);

if (is_admin()) {
    $settingsAdmin = new PluginSettingsAdmin(
        new PluginSettingsForm(new FileReader(__FILE__)),
        $settings,
        new JsonImporter());

    ProjectPlugin::getInstance()->addSettingsTab(new PluginSettingsTabAdapter($settingsAdmin));
} else {
    new GoogleTracker($settings);
}
