<?php
/*
Plugin Name: Open Content Everyware
Description: Open Content Everyware - Publish everything, everywhere. Let the reader choose.
Version: 2.0.4
Author: Naviga Web Team
Author URI: https://www.navigaglobal.com/web/
 */

/*
 * The first thing we need to do is to make sure the Everyware Plugin is loaded first
 */

use Everyware\DashboardWidget;

function everyware_plugin_first() {
	// ensure path to this file is via main wp plugin path
	$wp_path_to_this_file = preg_replace( '/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR . "/$2", __FILE__ );
	$this_plugin          = plugin_basename( trim( $wp_path_to_this_file ) );
	$active_plugins       = get_option( 'active_plugins' );
	$this_plugin_key      = array_search( $this_plugin, $active_plugins, true );
	if ( $this_plugin_key ) { // if it's 0 it's the first plugin already, no need to continue
		array_splice( $active_plugins, $this_plugin_key, 1 );
		array_unshift( $active_plugins, $this_plugin );
		update_option( 'active_plugins', $active_plugins );
	}
}
add_action( 'activated_plugin', 'everyware_plugin_first' );

const EVERY_VERSION = '2.0.4';
define( 'EVERY_BASE', plugin_dir_url(__FILE__) );
define( 'EVERY_DIR_PATH', plugin_dir_path( __FILE__) );

$ob_cache_ttl = getenv('PHP_OBJECT_CACHE_TTL') !== false && (int)getenv('PHP_OBJECT_CACHE_TTL') !== 0 ? (int)getenv('PHP_OBJECT_CACHE_TTL') : 86400;
define( 'PHP_OB_CACHE_TTL', $ob_cache_ttl );

global $use_oc_cache;
$use_oc_cache = TRUE;

// Setup languages
load_plugin_textdomain( 'every', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

//Every Admin/Login page
require_once __DIR__ . '/admin-style/admin-style.php';

new IncreaseWpSecurity();

//Create our custom post type (article)
new CustomOcPostType( __FILE__ );
new EveryArticleEvents( __FILE__ );

//Custom widget handler will sanitize/restore default WP-widgets when plugin is activated/deactivated
new CustomWidgetHandler( __FILE__ );
new WpExtension( __FILE__ );
new EveryStats( __FILE__ );

//Add menu to admin
if ( is_admin() ) {
    $oc = OpenContent::getInstance();
    $oc->oc_single_hook_init(__FILE__);
   
	if( filter_var($oc->getLogCacheEvents(), FILTER_VALIDATE_BOOLEAN) ) {
		new OcNotifierStats();
	}

	new CustomAjaxHandler();
	new PropertyMap( __FILE__ );

	new EnvSettings( __FILE__ );
	
	DashboardWidget::register();
}

$everyware_widgets = [
    'OcObjectsWidget',
    'OcImageWidget',
    'EveryMostReadWidget',
    'BoardRenderWidget'
];

add_action( 'widgets_init', function() use ($everyware_widgets) {
    foreach ( $everyware_widgets as $ew_widget ) {
        register_widget( $ew_widget );
    }
});


//Post Flush
add_action( 'before_delete_post', 'every_postcache_flush' );

/**
 * Function to remove an articles cache on deletion
 * @param $post_id
 */
function every_postcache_flush( $post_id ) {
    $uuid = get_post_meta($post_id, 'oc_uuid', true);

    if( !empty( $uuid )  ) {
        delete_transient( "pe_{$uuid}" );
        delete_transient( "the_post_{$uuid}" );
	    delete_transient( "the_post_id_{$uuid}" );
    }
}
