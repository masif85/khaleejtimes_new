<?php declare(strict_types=1);

/**
 * Redirect Original URLs Plugin
 *
 * @wordpress-plugin
 * Plugin Name: Redirect Original URLs
 * Description: Catches old content URL:s and redirects to their new URL:s.
 * Version: 1.1.0
 * Author: Naviga Content Lab AB
 * Author URI: http://www.navigaglobal.com/
 * Text Domain: ew-redirect-original-urls
 */

use Everyware\Plugin\RedirectOriginalUrls\Exceptions\OcSearchFailedException;
use Everyware\Plugin\RedirectOriginalUrls\OcMigratedUrlRepository;
use Everyware\Plugin\RedirectOriginalUrls\OriginalUrlsAnalysis;
use Everyware\Plugin\RedirectOriginalUrls\PluginSettings;
use Everyware\Plugin\RedirectOriginalUrls\PluginSettingsAdmin;
use Everyware\Plugin\RedirectOriginalUrls\PluginSettingsForm;
use Everyware\Plugin\RedirectOriginalUrls\OriginalUrlsRedirector;
use Everyware\ProjectPlugin\Components\Adapters\PluginSettingsTabAdapter;
use Everyware\ProjectPlugin\Helpers\FileReader;
use Everyware\ProjectPlugin\ProjectPlugin;
use Infomaker\Everyware\Support\Environment;
use Infomaker\Everyware\Twig\ViewSetup;

if (!defined('WPINC') || !class_exists(ProjectPlugin::class)) {
    exit;
}

if ( ! defined('REDIRECT_ORIGINAL_URLS_TEXT_DOMAIN')) {
    define('REDIRECT_ORIGINAL_URLS_TEXT_DOMAIN', 'ew-redirect-original-urls');
}

ViewSetup::getInstance()->registerTwigFolder('plugin-redirect-original-urls', __DIR__ . '/src/templates/');

add_action('wp_ajax_analyze_original_urls', static function() {
    $settings = PluginSettings::create();
    try {
        $analysis = new OriginalUrlsAnalysis(new OcAPI(), array_merge($settings->get(), [
            PluginSettings::ORIGINAL_URLS_PROPERTY_NAME => $_REQUEST[PluginSettings::ORIGINAL_URLS_PROPERTY_NAME],
            PluginSettings::URL_SETTING_DOMAINS         => PluginSettingsAdmin::parseDomains($_REQUEST[PluginSettings::URL_SETTING_DOMAINS])
        ]));

    } catch (OcSearchFailedException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        print $e->getMessage();
        exit;
    }

    header('Content-type: application/json');
    print json_encode($analysis->toArray());
    exit;
});

if (is_admin()) {
    add_action('current_screen', static function (WP_Screen $current_screen) {
        if ($current_screen->id !== 'toplevel_page_everyware-project-plugin') {
            return;
        }

        add_action('admin_enqueue_scripts', static function () {
            $scriptVersion = Environment::isDev() ? false : GIT_COMMIT;

            wp_enqueue_script(
                'redirect-original-urls-admin-js',
                plugin_dir_url(__FILE__) . 'dist/js/admin' . (is_dev() ? '.js' : '.min.js'),
                null,
                $scriptVersion
            );
            wp_enqueue_style(
                'redirect-original-urls-admin-css',
                plugin_dir_url(__FILE__) . 'dist/css/admin' . (is_dev() ? '.css' : '.min.css'),
                null,
                $scriptVersion
            );
        });

        $settings = PluginSettings::create();
        $settingsAdmin = new PluginSettingsAdmin(
            new PluginSettingsForm(new FileReader(__FILE__)),
            $settings
        );

        ProjectPlugin::getInstance()->addSettingsTab(new PluginSettingsTabAdapter($settingsAdmin));
    });
} else {
    /** @var int $priority  Needs to be lower than 10 to run before any Domain Mapping plugin. */
    $priority = 9;

    add_action('template_redirect', static function() {
        $repository = new OcMigratedUrlRepository(new OcAPI());
        new OriginalUrlsRedirector($repository);
    }, $priority);
}
