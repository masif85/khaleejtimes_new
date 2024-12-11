<?php declare(strict_types=1);

/**
 * Plugin Name: Everyware Project Plugin
 * Description: Everyware Project plugin which contains widgets and plugins for the sites
 * Version: 0.10.0
 * Author:      Naviga Web Team
 * Author URI:  https://www.navigaglobal.com/web/
 */

use Everyware\ProjectPlugin\Config;
use Everyware\ProjectPlugin\ProjectPlugin;
use Everyware\ProjectPlugin\TwigSetup;

// Check so we don't give direct access to file.
if ( ! defined('WPINC')) {
    die;
}

TwigSetup::registerTwigFolder('projectPlugin', __DIR__ . '/src/templates');
TwigSetup::addWpFunction('__', 'translate');

ProjectPlugin::bootstrap(new Config(basename(__DIR__)));
