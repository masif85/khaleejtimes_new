<?php

/*
|--------------------------------------------------------------------------
| This class i responsible for creating a unique site id
| and registering the site on the OC Notifier
| the Events will be posted to a address similar to this:
| http://192.168.1.134/~admin/wp/breaking/wp-content/plugins/Every/oc_event_listener.php
|
| It will also register and un-register widgets as subscribers
| When a Widget is registered it will be added as a single post in WP DB to keep track of the unique listener
| It will also add the Widget ID into a collection in WB DB to be looped over when Notifier is deactivated entirely
|--------------------------------------------------------------------------
*/

class OcNotifier {

	//Prefixes for WP DB
	//Unique Site id can be found here
	static $NOTIFIER_ID_OPTION_NAME = 'oc_notifier_id';

	//Boolean, will be set to true if site is registered
	static $NOTIFIER_IS_REGISTERED_OPTION = 'oc_notifier_registered';

	//All registered widgets are stored with this prefix + widget ID
	static $WIDGET_REGISTERED_PREFIX = 'oc_notifier_widget_';

	//Every time a widget is added as subscriber, it also added in this list to keep track of all active subscribers
	static $ALL_REGISTERED_NOTIFIER_WIDGETS = 'oc_notifier_widgets';

	//Unique settings for each site
	private $notifier_base_url;
	private $unique_id;

	//Public getter for the Notifier URL
	public function get_notifier_base_url() {
		return isset( $this->notifier_base_url ) ? $this->notifier_base_url : null;
	}

	//Public getter for the unique site id
	public function get_unique_id() {
		return isset( $this->unique_id ) ? $this->unique_id : null;
	}

	/*
	 * Public constructor
	 * will make sure we have a unique ID and then register our site as a listener
	 *
	 * @param Notifier URL
	 */
	public function __construct( $_subscription_url = "" ) {

		if ( $_subscription_url !== '' ) {
			$this->notifier_base_url = $_subscription_url;
		} else {
			$options = get_option( 'oc_options' );
			isset( $options['oc_notifier'] ) ? $this->notifier_base_url = $options['oc_notifier'] : $this->notifier_base_url = null;
		}

		$this->set_unique_site_id();
	}

	/*
	 * Function to check if given Notifier URL is valid
	 *
	 * @param -
	 * @return Boolean - success
	 */
	public function check_if_valid_url() {
		//Make sure the URL is ok
		$url         = $this->notifier_base_url . 'status';
		$response    = wp_remote_get( $url );
		$http_status = wp_remote_retrieve_response_code( $response );
		echo $http_status;
		if ( $http_status == "200" ) {
			return true;
		} else {
			return false;
		}
	}

	/*
	 * Private function to get unique ID from DB
	 * If it does not exist it will call create_unique_id method to generate a new one
	 *
	 * @param -
	 * @return -
	 */
	private function set_unique_site_id() {
		$id = get_option( self::$NOTIFIER_ID_OPTION_NAME );

		if ( isset( $id ) && $id !== '' && $id !== false ) {
			$this->unique_id = $id;
		} else {
			$this->create_unique_id();
			update_option( self::$NOTIFIER_ID_OPTION_NAME, $this->unique_id );
		}
	}

	/*
	 * Private function to create and set a unique OC Notifier ID to the site
	 *
	 * @param -
	 * @return -
	 */
	private function create_unique_id() {
		$site_name = bloginfo( 'name' );

		if ( $site_name !== '' ) {
			$this->unique_id = uniqid( $site_name, true );
		} else {
			$this->unique_id = uniqid( '', true );
		}
	}

	/*
	 * Function to register SITE as listener
	 *
	 * @param -
	 * @return Boolean - success
	 */
	function register_site_as_listener() {
		//get_option Will return false if we are not registered
		$is_registered = get_option( self::$NOTIFIER_IS_REGISTERED_OPTION );

		//If we are not registered, do it!!
		if ( ! $is_registered ) {
			$args = array(
				'method'  => 'POST',
				'headers' => array(),
				'body'    => array(
					'name'       => $this->unique_id,
					'notify_url' => plugins_url() . '/Every/oc_event_listener.php',
					'type'       => 'query',
					'ignore'     => ''
				)
			);

			$url      = $this->notifier_base_url . 'subscribers';
			$response = wp_remote_post( $url, $args );

			//If something went wrong, make sure WP DB has no entry
			if ( is_wp_error( $response ) ) {
				$this->purge_wp_notifier_subscription();
				return false;

			} else {
				//Success! store it in WP DB
				update_option( self::$NOTIFIER_IS_REGISTERED_OPTION, true );
				return true;
			}
		}
		//We are already registered, wont do it twice!
		return false;
	}

	/*
	 * Function to UN-register SITE as listener
	 *
	 * @param -
	 * @return Boolean - success
	 */
	function un_register_site_as_listener() {
		//get_option Will return false if we are not registered
		$is_registered = get_option( self::$NOTIFIER_IS_REGISTERED_OPTION );

		//If we are registered, un register!!
		if ( $is_registered !== false ) {
			$args = array(
				'method'  => 'DELETE',
				'headers' => array(),
				'body'    => array()
			);

			$url      = $this->notifier_base_url . 'subscribers/' . $this->unique_id;
			$response = wp_remote_post( $url, $args );

			//If something went wrong, return false
			if ( is_wp_error( $response ) ) {

				return false;

			} else {
				//Success! Remove entry from WP DB
				$this->purge_wp_notifier_subscription();
				return true;
			}
		}

		//We can not un-register when we are not registered
		return false;
	}

	/*
	 * Function to purge WP DB, unset subscribers
	 * WILL NOT unset subscription on OC Notifier, only purge WP DB
	 * Will remove entry of site as listener and all widgets as listeners, from WP DB
	 *
	 * @param -
	 * @return boolean - success
	 */
	public function purge_wp_notifier_subscription() {
		$all_subscribing_widgets = get_option( self::$ALL_REGISTERED_NOTIFIER_WIDGETS );

		if ( $all_subscribing_widgets !== false ) {
			$widgets_array = json_decode( $all_subscribing_widgets );

			foreach ( $widgets_array as $widget_id ) {
				delete_option( self::$WIDGET_REGISTERED_PREFIX . $widget_id );
			}

		}

		delete_option( self::$ALL_REGISTERED_NOTIFIER_WIDGETS );
		return delete_option( self::$NOTIFIER_IS_REGISTERED_OPTION );
	}

	/*
	 * Function to register WIDGET as subscriber
	 * Used by widget base when checkbox is checked
	 *
	 * @param widget id
	 * @return Boolean - success
	 */
	public function subscribe_widget( $widget_id, $query_string, $page_id ) {
		$is_registered = get_option( self::$WIDGET_REGISTERED_PREFIX . $widget_id );

		if ( ! isset( $page_id ) || $page_id === '' ) {
			$page_id = - 1; //Prefabed widget, no active page
		}

		//If we are not registered, do it!
		if ( ! $is_registered ) {
			$args = array(
				'method'  => 'POST',
				'headers' => array(),
				'body'    => array(
					'name'         => $widget_id,
					'query_string' => $query_string,
					'page_id'      => $page_id
				)
			);

			$url      = $this->notifier_base_url . 'subscribers/' . $this->unique_id;
			$response = wp_remote_post( $url, $args );

			//If something went wrong
			if ( is_wp_error( $response ) ) {
				return false;

			} else {
				//Success! Add to WP DB
				update_option( self::$WIDGET_REGISTERED_PREFIX . $widget_id, true );
				$this->add_subscriber_to_list( $widget_id );
				return true;
			}
		}

		return false;

	}

	/*
	 * Function to UN-register a WIDGET as subscriber
	 * Used by widget base when checkbox is unchecked
	 *
	 * @param widget id
	 * @return -
	 */
	public function unsubscribe_widget( $widget_id ) {
		$is_registered = get_option( self::$WIDGET_REGISTERED_PREFIX . $widget_id );

		if ( $is_registered ) {
			$args     = array(
				'method'  => 'DELETE',
				'headers' => array(),
				'body'    => array()
			);
			$url      = $this->notifier_base_url . 'subscribers/' . $this->unique_id . '/' . $widget_id;
			$response = wp_remote_post( $url, $args );

			//If something went wrong
			if ( is_wp_error( $response ) ) {
				return false;

			} else {
				//Success! Remove from WP DB
				delete_option( self::$WIDGET_REGISTERED_PREFIX . $widget_id );
				$this->extract_subscriber_from_list( $widget_id );
				return true;
			}
		}

		return false;
	}

	/*
	 * Function to keep track of all added widgest
	 *
	 * @param widget ID
	 */
	public function add_subscriber_to_list( $widget_id ) {
		//Get all added widgets
		$added_widgets = get_option( self::$ALL_REGISTERED_NOTIFIER_WIDGETS );

		//If none
		if ( $added_widgets == false ) {
			$added_widgets = array( $widget_id );
			$widget_json   = json_encode( $added_widgets );
			update_option( self::$ALL_REGISTERED_NOTIFIER_WIDGETS, $widget_json );
		} else {
			$widget_array = json_decode( $added_widgets );
			$key          = array_search( $widget_id, $widget_array );
			if ( $key === false ) {
				array_push( $widget_array, $widget_id );
				$widget_json = json_encode( $widget_array );
				update_option( self::$ALL_REGISTERED_NOTIFIER_WIDGETS, $widget_json );
			}
		}
	}

	/*
	 * Function to extract widget from collection in WP DB
	 * This function will not notify OC Notifier
	 * Only extract given widget_id from WPs own collection of subscribers
	 *
	 * @param Widget ID
	 */
	public function extract_subscriber_from_list( $widget_id ) {
		//Get all added widgets
		$added_widgets = get_option( self::$ALL_REGISTERED_NOTIFIER_WIDGETS );

		//If we get any hits
		if ( $added_widgets !== false ) {
			$widget_array = json_decode( $added_widgets );
			$key          = array_search( $widget_id, $widget_array );
			if ( $key !== false ) {
				unset( $widget_array[$key] );

				$widget_json = json_encode( $widget_array );
				update_option( self::$ALL_REGISTERED_NOTIFIER_WIDGETS, $widget_json );
			}

		}

		return true;
	}
}