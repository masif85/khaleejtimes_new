<?php

/**
 * Plugin Name: Menu Handler
 * Description: Plugin for handling menus (adding custom fields, etc.).
 * Version: 0.1.3
 * Author: Infomaker Scandinavia AB
 * Author URI: https://infomaker.se
 */

use Everyware\Plugin\MenuHandler\PluginSettings;
use Everyware\ProjectPlugin\Components\Adapters\PluginSettingsTabAdapter;
use Everyware\ProjectPlugin\Components\SettingsHandler;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\ProjectPlugin;
use Infomaker\Everyware\Support\Str;
use Infomaker\Everyware\Twig\ViewSetup;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;

if ( ! defined('WPINC') || ! class_exists(ProjectPlugin::class)) {
    exit;
}

$fileReader = new FileReader(__FILE__);

$pluginName = $fileReader->getHeader('Plugin Name');

$settings = PluginSettings::create();

include_once( 'src/components/class-walker-nav-menu-edit-custom.php' );

if (is_admin()) {

    $settingsHandler = new SettingsHandler(Str::slug('ew-' . $pluginName));

}