<?php

class IncreaseWpSecurity {

	public function __construct() {

		//Remove WP version
		add_filter( 'the_generator', array( &$this, 'every_remove_wp_version' ) );

		//Require correct WP version
		add_action( 'admin_init', array( &$this, 'every_required_wordpress_version' ) );

		//Remove Error message on bad login
		add_filter( 'login_errors', array( &$this, 'every_failed_login' ) );
	}

	/*
	 * Public function to remove WP version from rendered sites
	 */
	public function every_remove_wp_version() {
		return '';
	}

	/*
	 * A function that will make sure that the current version of WP is at least 3.4
	 * Will display an error page if not, linking back to admin or to update wp page on wordpress.org
	 * Will also make sure that the Everyware plugin is properly deactivated
	 */
	public function every_required_wordpress_version( ) {
		global $wp_version;
		$plugin      = plugin_basename( __FILE__ );
		$plugin_data = get_plugin_data( __FILE__, false );

		if ( version_compare( $wp_version, "3.4", "<" ) ) {
			if ( is_plugin_active( $plugin ) ) {
				deactivate_plugins( $plugin );
				wp_die( "The " . $plugin_data['Name'] . " plugin requires WordPress 3.4 or higher! <br /><br />The Plugin will be deactivated.<br /><br />Back to <a href='" . admin_url() . "plugins.php'>WordPress admin</a>. Or read more about  <a href='http://codex.wordpress.org/Upgrading_WordPress'>Updating WordPress.</a>" );
			}
		}
	}

	/*
	 * Function to render safer Error message on Bad-login
	 */
	public function every_failed_login() {
		return 'Incorrect credentials entered!';
	}
}