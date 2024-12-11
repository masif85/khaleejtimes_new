<?php declare(strict_types=1);

/**
 * Plugin Name: Sitemap plugin
 * Description: Dispaly latest articles for sitemap
 * Version: unreleased
 * Author: Naviga Content Lab AB
 * Author URI: http://www.navigaglobal.com/
 * Text Domain: ew-sitemap
 */

use Everyware\Plugin\Sitemap\Sitemap;
use Infomaker\Everyware\Twig\ViewSetup;
use Everyware\ProjectPlugin\ProjectPlugin;

if ( ! defined('WPINC') || ! class_exists(ProjectPlugin::class)) {
  exit;
}

ViewSetup::getInstance()->registerTwigFolder('sitemapPlugin', __DIR__ . '/src/templates/');

add_action('init', static function() {
  // Make sure Everyware plugin is active.
  if ( ! defined('EVERY_VERSION')) {
    return;
  }

  Sitemap::init();
});
