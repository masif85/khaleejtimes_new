<?php
/*
 * This class is responsible for removing the default WP widgets from pages upon activation of Every plugin
 * It also restores default WP widgets upon deactivation of Every plugin
 */
class CustomWidgetHandler {

	function __construct( $file ) {
		//Make sure we sanitize the default widgets when activating
		#register_activation_hook( $file, array( &$this, 'cwh_set_every_defaults' ) );

		//And reset WP deafults when de-activating
		#register_deactivation_hook( $file, array( &$this, 'cwh_reset_wp_default' ) );
	}

	function cwh_set_every_defaults() {
		update_option( 'sidebars_widgets', '' );
	}

	function cwh_reset_wp_default() {
		update_option( 'sidebars_widgets', array(
			'wp_inactive_widgets' => array(),
			'sidebar-1'           => array( 0 => 'search-2', 1 => 'recent-posts-2', 2 => 'recent-comments-2', 3 => 'archives-2', 4 => 'categories-2', 5 => 'meta-2', ),
			'sidebar-2'           => array(),
			'sidebar-3'           => array(),
			'sidebar-4'           => array(),
			'sidebar-5'           => array(),
			'array_version'       => 3 ) );
	}
}