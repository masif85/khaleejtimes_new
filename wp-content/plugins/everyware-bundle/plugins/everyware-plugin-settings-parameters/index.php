<?php

/**
 * Plugin Name: Settings Parameters
 * Description: Allows use of global parameters
 * Version: 0.2.3
 * Author: Infomaker Scandinavia AB
 * Author URI: https://infomaker.se
 */

use Everyware\Plugin\SettingsParameters\PluginSettingsForm;
use Everyware\Plugin\SettingsParameters\PluginSettingsParameters;
use Everyware\ProjectPlugin\Components\Adapters\PluginSettingsTabAdapter;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\Components\ComponentAdmin;
use Everyware\ProjectPlugin\ProjectPlugin;
use Infomaker\Everyware\Twig\ViewSetup;

if (!defined('WPINC') || !class_exists(ProjectPlugin::class)) {
    exit;
}

ViewSetup::getInstance()->registerTwigFolder('plugins', __DIR__.'/src/templates/');

if (is_admin()) {
    $settings = new PluginSettingsParameters();
    $pluginSettingsTab = new ComponentAdmin(
        new PluginSettingsForm(new FileReader(__FILE__)),
        $settings
    );
    $settings->init();
    ProjectPlugin::getInstance()->addSettingsTab(new PluginSettingsTabAdapter($pluginSettingsTab));
}
