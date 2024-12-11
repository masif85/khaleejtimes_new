<?php declare(strict_types=1);

/*
Plugin Name: Naviga Content Sync
Description: Plugin to handle events from the Naviga Content Sync service.
Version: 1.0.1
Network: true
Author: Naviga Web Team
Author URI: https://www.navigaglobal.com/web/
Text Domain: content-sync
*/

use Everyware\Plugin\ContentSync\AwsProvider;
use Everyware\Plugin\ContentSync\ConfigManager;
use Everyware\Plugin\ContentSync\Plugin;
use Everyware\Plugin\ContentSync\Response;
use Everyware\Plugin\ContentSync\Router;
use Everyware\Plugin\ContentSync\Settings;
use Everyware\Plugin\ContentSync\SettingsPage;
use Everyware\Plugin\ContentSync\Wordpress\WpAdmin;
use Everyware\Plugin\ContentSync\Wordpress\WpNetworkOptions;
use Everyware\Plugin\ContentSync\Wordpress\WpNotices;
use Everyware\Plugin\ContentSync\Wordpress\WpSites;
use Infomaker\Everyware\Support\Environment;

if ( ! defined('WPINC')) {
    exit;
}

const CONTENT_SYNC_LANG = 'content-sync';

add_action('wp_loaded', function () {
    $path = parse_url(add_query_arg([]), PHP_URL_PATH) ?: '/';

    if (trim($path, '/') === ConfigManager::API_PATH) {
        $router = new Router(new Response());
        $router->handleEvent();
    }
});

try {
    AwsProvider::validateEnvironment();

    add_action('init', function () {

        $pluginData = get_plugin_data(__FILE__, false, false);
        $env = Environment::current();

        define('CONTENT_SYNC_LAST_DEPLOYED', "content_sync_{$env}_deployed");
        define('CONTENT_SYNC_PLUGIN_VERSION', $pluginData['Version']);

        $settings = new Settings(AwsProvider::createS3Provider(), new WpSites(), new WpNetworkOptions());

        $plugin = new Plugin(new WpNetworkOptions(), $settings);

        $plugin->deploySettings();

        if ( ! Environment::isDev()) {
            $plugin->initHooks();
        }

        $settingsPage = new SettingsPage($settings, new WpSites());
        $settingsPage->registerToWordpress(new WpAdmin());
    });
} catch (Exception $e) {
    $notice = new WpNotices($e);
    add_action('admin_notices', [$notice, 'renderSiteNotice']);
    add_action('network_admin_notices', [$notice, 'renderSiteNotice']);
}

// Hide from site plugins
add_filter('all_plugins', function ($plugins) {
    if (is_multisite() && ! is_network_admin()) {
        unset($plugins[basename(__DIR__) . '/' . basename(__FILE__)]);
    }

    return $plugins;
});
