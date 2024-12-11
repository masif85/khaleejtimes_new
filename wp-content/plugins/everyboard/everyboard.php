<?php
/*
Plugin Name: EveryBoard
Depends: Open Content Everyware
Description: Plan, publish and win.
Version: 2.2.0
Author: Naviga Web Team
Author URI: https://www.navigaglobal.com/web/
Text Domain: everyboard
 */

use Everyware\Everyboard\DashboardWidget;
use Everyware\Everyboard\Widgets\ClientValidator;

/**
 * Contains the current version-number of Everyboard
 */

const EVERYBOARD_VERSION = '2.2.0';
const EVERYBOARD_SCRIPT_VERSION = '2.2.0';
const EVERYBOARD_JSON_FORMAT_VERSION = '1.0';
const EVERYBOARD_EW_DEPENDENCY_MIN_VERSION = '1.7.3';

/**
 * Check that the OcAPI-class exists
 * We do this to confirm that the Every-plugin has been activated
 */

load_plugin_textdomain('everyboard', false, dirname(plugin_basename(__FILE__)) . '/languages/');

$notice = everyboard_check_dependencies();
if (empty($notice)) {
    define('EVERYBOARD_BASE', plugin_dir_url(__FILE__));
    define('EVERYBOARD_DIR_PATH', plugin_dir_path(__FILE__));

    // Setup languages
    require_once __DIR__ . '/widgetprefab/widgetprefab.php';
    require_once __DIR__ . '/board/board.php';
    require_once __DIR__ . '/settings/settings.php';

    $global_settings = new EveryBoard_Global_Settings(__FILE__);

    if (EveryBoard_Global_Settings::is_everylist_active()) {
        require_once __DIR__ . '/list/list.php';
    }

    add_action('init', function () {
        if (EveryBoard_Global_Settings::is_oclist_active()) {
            OcList::init();
        }
    });

    DashboardWidget::register();

    add_action('wp_ajax_oc_client_validation', static function () {
        ClientValidator::validateClientData();
    });

} else {
    add_action('admin_init', 'everyboard_deactivate');
    add_action('admin_notices', function () use ($notice) {
        everyboard_deactivation_notice($notice);
    });
}

/**
 * Function to check dependencies for Everyboard
 */
function everyboard_check_dependencies()
{
    $notice = '';

    // Check if Everyware has been activated
    if ( ! everyboard_is_everyware_activated()) {
        $notice = __('<strong>EveryBoard</strong> needs the <strong>Open Content Everyware</strong> plugin to be activated and has therefore been <strong>deactivated</strong>.
            <br> Activate <strong>Open Content Everyware</strong> and try again', 'everyboard');
    } else {
        if ( ! everyboard_dependent_ew_version_loaded()) {
            $eb_version = EVERYBOARD_VERSION;
            $ew_min_version = EVERYBOARD_EW_DEPENDENCY_MIN_VERSION;
            $notice = __("<strong>EveryBoard {$eb_version}</strong> needs the <strong>Open Content Everyware version {$ew_min_version} </strong> or higher to be activated and has therefore been <strong>deactivated</strong>.",
                "everyboard");
        }
    }

    return $notice;
}

/**
 * @return bool
 *
 * Function to determine if a minimum version of everyware is loaded for everyboard
 */
function everyboard_dependent_ew_version_loaded()
{
    if ( ! everyboard_is_everyware_activated()) {
        return false;
    }

    return version_compare(EVERY_VERSION, EVERYBOARD_EW_DEPENDENCY_MIN_VERSION, '>=');
}

/**
 * Function to deactivate EveryBoard
 */
function everyboard_deactivate()
{
    deactivate_plugins(plugin_basename(__FILE__));
}

if ( ! function_exists('get_sites')) {
    /**
     * Helper for WP-versions that don't yet have converted wp_get_sites
     *
     * @return array
     */
    function get_sites()
    {
        return array_map(function ($site) {
            return (object)$site;
        }, wp_get_sites());
    }
}

/**
 * Function to notice the user when EveryBoard has been deactivated
 *
 * @param string $notice
 */
function everyboard_deactivation_notice(string $notice)
{
    # Start HTML
    ?>
    <div class="updated">
        <p>
            <?php echo $notice; ?>
        </p>
    </div>

    <?php
    # End HTML
    echo '';
    if (isset($_GET['activate'])) {
        unset($_GET['activate']);
    }
}

function everyboard_is_everyware_activated(): bool
{
    return defined('EVERY_VERSION');
}

add_action('admin_enqueue_scripts', function () {

    $widgets_access = get_user_setting('widgets_access');
    if (isset($_GET['widgets-access'])) {
        check_admin_referer('widgets-access');

        $widgets_access = 'on' == $_GET['widgets-access'] ? 'on' : 'off';
        set_user_setting('widgets_access', $widgets_access);
    }

    if ('on' == $widgets_access) {
        add_filter('admin_body_class', 'wp_widgets_access_body_class');
    } else {
        wp_enqueue_script('admin-widgets');

        if (wp_is_mobile()) {
            wp_enqueue_script('jquery-touch-punch');
        }
    }
});

global $pagenow;
if ($pagenow === 'widgets.php' && is_admin()) {

    add_action('admin_enqueue_scripts', function () {

        wp_enqueue_style(
            'ew-widget-extension-style',
            EVERYBOARD_BASE . 'assets/dist/css/boardwidgetextension.min.css',
            [],
            EVERYBOARD_VERSION
        );
        wp_enqueue_script(
            'everyboard-widget-extension',
            EVERYBOARD_BASE . 'assets/dist/js/widget-extension.min.ugly.js',
            ['jquery'],
            EVERYBOARD_VERSION
        );
        wp_enqueue_script(
            'every-selectize',
            EVERYBOARD_BASE . 'assets/dist/js/selectize.js',
            ['jquery'],
            EVERYBOARD_VERSION
        );
        wp_localize_script('everyboard-widget-extension', 'widgetTags', get_option('ew_board_wiget_tags'));
        wp_localize_script('everyboard-widget-extension', 'translation_widget_extension', [
            'confirm_move_widget' => __('Are you sure you want to move the widget from the Everyboard sidebar? Any active instance of the widget will be removed from the boards.',
                "everyboard")
        ]);
    });
}
