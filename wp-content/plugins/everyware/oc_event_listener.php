<?php

/*
|--------------------------------------------------------------------------
| This file is used by OC Event Notifier to purge cache on Widgets
| Will clear WP Transient-cache on widgets by widget ID
| Will NOT re-fill cache, this is done when page is requested again
|
| Three custom hooks available:
| oc_event_listener_purge - runs before looping through widgets, no widget ID available yet
| oc_event_listener_pre_purge - runs inside the loop, execute on widget is purged, before the wp-cache has been purged
| oc_event_listener_post_purge - runs inside the loop, execute on widget is purged, after the wp-cache has been purged
|
| Hook into them like this:
| add_action('oc_event_listener_pre_purge', 'name_of_function_to_run', 10, 2); //Two args: $widget_id, $page_id
| add_action('oc_event_listener_post_purge', 'name_of_function_to_run', 10, 2); //Two args: $widget_id, $page_id
|--------------------------------------------------------------------------
*/

//If post comes from OC Event Notifier, get the json
if ( isset( $_POST['message'] ) ) {

	//Decode it
	$message = json_decode( $_POST['message'] );

	//make sure we have the 'widgets' property
	if ( property_exists( $message, 'widgets' ) ) {

		//Bootstrap WordPress
		define( 'WP_USE_THEMES', false );
		require( '../../../wp-blog-header.php' );

		//Init OcApi
		$oc_api = new OcAPI();

		//Fire of all functions hooked into oc_event_listener_purge
		do_action( 'oc_event_listener_purge' );

		//Loop through all IDs and flush WP-cache
		foreach ( $message->widgets as $widget ) {

			//Fire of all functions hooked into pre_purge
			do_action( 'oc_event_listener_pre_purge', $widget->page_id, $widget->query, $widget->widget_id );

			//Flush cache
			$oc_api->update_widget_cache( $widget->query );

			//Fire of all functions hooked into post_purge
			do_action( 'oc_event_listener_post_purge', $widget->page_id, $widget->query, $widget->widget_id );

		}
	}

	//Its there, Set headers to OK
	header( "HTTP/1.1 200 OK" );

} else {
	//Cant find prop, set error headers
	header( "HTTP/1.0 400 Bad Request" );
}
