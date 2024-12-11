<?php declare(strict_types=1);

/*
Plugin Name: Everyware RSS Feeds
Description: Everyware RSS Feeds is a plugin that lets you handle RSS feeds inside Wordpress.
Version: 1.0.1
Author: Naviga Content Lab AB
Author URI: http://www.navigaglobal.com/
Text Domain: ew-rss-feeds
 */

use Everyware\RssFeeds\FeedSettings;
use Everyware\RssFeeds\ItemSettings;
use Everyware\RssFeeds\OcApiHandler;
use Everyware\RssFeeds\RssFeedAdmin;
use Everyware\RssFeeds\RssFeeds;
use Infomaker\Everyware\Support\Environment;
use Infomaker\Everyware\Twig\ViewSetup;

// Check so we don't give direct access to file.
if ( ! defined('WPINC')) {
    die;
}

if ( ! defined('RSS_FEEDS_TEXT_DOMAIN')) {
    define('RSS_FEEDS_TEXT_DOMAIN', 'ew-rss-feeds');
}

ViewSetup::getInstance()->registerTwigFolder('rssFeedsPlugin', __DIR__ . '/src/templates/');

add_action('init', static function() {
    // Make sure Everyware plugin is active.
    if ( ! defined('EVERY_VERSION')) {
        return;
    }

    RssFeedAdmin::init();

});

add_action('wp_ajax_validate_oc_list', static function() {
    $response = FeedSettings::validateOcList(new OcApiHandler());
    print json_encode($response->toArray());
    exit;
});

add_action('wp_ajax_validate_oc_query', static function() {
    $response = FeedSettings::validateOcQuery(new OcApiHandler());
    print json_encode($response->toArray());
    exit;
});

// Actions that should not or need not affect pages outside this plugin.
add_action('current_screen', static function (WP_Screen $current_screen){

    if ($current_screen->post_type !== RssFeeds::POST_TYPE_ID) {
        return;
    }

    add_action('admin_enqueue_scripts', static function () {
        $scriptVersion = Environment::isDev() ? false : GIT_COMMIT;

        wp_enqueue_script(
            'rss-feeds-admin-js',
            plugin_dir_url(__FILE__) . 'dist/js/admin' . (is_dev() ? '.js' : '.min.js'),
            null,
            $scriptVersion
        );
        wp_enqueue_style(
            'rss-feeds-admin-css',
            plugin_dir_url(__FILE__) . 'dist/css/admin' . (is_dev() ? '.css' : '.min.css'),
            null,
            $scriptVersion
        );
    });

    add_filter('gettext', static function ($translated, $original, $domain) {
        if ($domain === 'default') {
            if ($original === 'Preview Changes' || $original === 'Preview') {
                $translated = 'View XML';
            }
        }

        return $translated;

    }, 10, 3);

    new FeedSettings('Feed settings');
    new ItemSettings('Item settings');
});

add_filter('single_template_hierarchy', static function ($page_templates) {

    return RssFeeds::modifyTemplateHierarchy($page_templates);

});

