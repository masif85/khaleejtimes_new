<?php declare(strict_types=1);

/*
Plugin Name: Everyware Concepts
Description: Everyware Concepts is a plugin that lets you handle Concepts inside Wordpress.
Version: 0.2.6
Author: Infomaker Scandinavia AB
Author URI: http://www.infomaker.se
Text Domain: ew-concepts
Domain Path: /languages/
 */

use Everyware\Concepts\Admin\DuplicatesPage;
use Everyware\Concepts\Admin\OpenContentClient;
use Everyware\Concepts\Admin\ErrorPage;
use Everyware\Concepts\AjaxAPI;
use Everyware\Concepts\ConceptController;
use Everyware\Concepts\ConceptAdmin;
use Everyware\Concepts\ConceptDiffProvider;
use Everyware\Concepts\ConceptDuplicatesProvider;
use Everyware\Concepts\Concepts;
use Everyware\Concepts\OcConceptProvider;
use Everyware\Concepts\Wordpress\Action;
use Infomaker\Everyware\Support\Storage\CollectionDB;
use Infomaker\Everyware\Twig\ViewSetup;

// Check so we don't give direct access to file.
if ( ! defined('WPINC')) {
    die;
}

add_action('admin_notices', static function () {
    $messages = [];

    if ( ! defined('EVERY_VERSION')) {
        $messages[] = '<strong>Concepts</strong> will only be available once the "Open Content Everyware" plugin has been activated and configured.';
    }

    foreach ($messages as $message) {
        print '<div class="notice notice-warning is-dismissible"><p>' . $message . '</p></div>';
    }
});

if ( ! defined('CONCEPTS_TEXT_DOMAIN')) {
    define('CONCEPTS_TEXT_DOMAIN', 'ew-concepts');
}

// Plugin Folder Path.
if ( ! defined('CONCEPTS_PLUGIN_URL')) {
    define('CONCEPTS_PLUGIN_URL', plugin_dir_url(__FILE__));
}

ViewSetup::getInstance()->registerTwigFolder('conceptsPlugin', __DIR__ . '/src/templates/');

add_action('init', static function () {

    // Make sure Everyware plugin is active.
    if ( ! defined('EVERY_VERSION')) {
        return;
    }
    
    $conceptAdmin = ConceptAdmin::init();

    $diffProvider = new ConceptDiffProvider(
        new CollectionDB('ew_concepts_diff'),
        OpenContentClient::createFromWpSettings(OpenContent::getInstance())
    );

    $duplicatesProvider = new ConceptDuplicatesProvider(new CollectionDB('ew_concepts_duplicates'));

    $conceptAdmin->addSubPage(new ErrorPage($diffProvider));
    $conceptAdmin->addSubPage(new DuplicatesPage($duplicatesProvider));
    AjaxAPI::init();
});

add_action('plugins_loaded', static function () {

    // Make sure Everyware plugin is active.
    if ( ! defined('EVERY_VERSION')) {
        return;
    }

    $controller = new ConceptController(
        Concepts::init(),
        new OcConceptProvider(new OcAPI()),
        new Action()
    );

    AjaxAPI::get('show', [$controller, 'show']);
    AjaxAPI::post('create', [$controller, 'create']);
    AjaxAPI::post('delete', [$controller, 'remove']);
    AjaxAPI::post('update', [$controller, 'update']);
    AjaxAPI::post('sync', [$controller, 'synchronize']);

    load_muplugin_textdomain(CONCEPTS_TEXT_DOMAIN, dirname(plugin_basename(__FILE__)) . '/languages/');
});
