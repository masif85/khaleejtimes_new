<?php

define( 'WP_USE_THEMES', false );
require_once( __DIR__ . '/../../../../wp-blog-header.php' );
require_once( 'open_content.php' );
require_once( 'oc_api.php' );

status_header(200);

$inputJSON = file_get_contents('php://input');

if( validate_remote_token() && $inputJSON !== '' ) {
    new OcPush( $inputJSON );
}

function validate_remote_token() {
    $token = md5( strtolower( plugin_dir_url(__FILE__) . 'oc_push.php') );
    $remote_token = $_GET['token'];

    return $token === $remote_token;
}

class OcPush {
    const OC_ADD = 'ADD';
    const OC_UPDATE = 'UPDATE';
    const OC_DELETE = 'DELETE';
	const OC_PUSH_DEBUG = 'OC_PUSH_DEBUG';

    private $oc;
    private $raw_response;
    private $decoded_response;
    private $contenttype;
    private $raw_contenttype;
    private $eventtype;
    private $uuid;

    public function __construct( $input ) {

        $this->raw_response = $input;
        $this->oc = new OcAPI();
        $this->decoded_response = json_decode( $input );
        $this->contenttype = strtolower( $this->decoded_response->contentType );
        $this->raw_contenttype = $this->decoded_response->contentType;
        $this->eventtype = $this->decoded_response->eventtype;
        $this->uuid = $this->decoded_response->uuid;

        // Unless the object has been deleted we fetch data to get hierarchical properties as well.
        if ($this->eventtype !== self::OC_DELETE) {
            $object_data = $this->get_object_data();
            if (isset($object_data) && is_string($object_data)) {
                $this->raw_response = $object_data;
            }
        }
        try {
            switch ( $this->eventtype ) {
                case self::OC_ADD:
                    $this->on_oc_add();
                    break;
                case self::OC_UPDATE:
                    $this->on_oc_update();
                    break;
                case self::OC_DELETE:
                    $this->on_oc_delete();
                    break;
            }
        } catch (Exception $e) {
            trigger_error( 'Error when trying to handle notification with message: '. $e->getMessage(). ' - Trace: ' . $e->getTraceAsString(), E_USER_ERROR );
        }

        $this->add_to_debug();
    }

	private function add_to_debug() {
		$time = time();
		$current_debug = json_decode( get_transient( self::OC_PUSH_DEBUG ), true );

		if( $current_debug !== false && !empty( $current_debug ) ) {
			if( count( $current_debug ) > 4 ) {
				array_pop( $current_debug );
			}

			array_unshift( $current_debug, array( 'time' => $time, 'content' => $this->contenttype, 'uuid' => $this->uuid, 'event' => $this->eventtype ) );
			set_transient( self::OC_PUSH_DEBUG, json_encode( $current_debug ) );
		} else {
			set_transient( self::OC_PUSH_DEBUG, json_encode( array ( array( 'time' => $time, 'content' => $this->contenttype, 'uuid' => $this->uuid, 'event' => $this->eventtype ) ) ) );
		}

	}

    private function on_oc_update() {
        $push_response = $this->get_push_response();
    
        /**
         * Fires before the push has been updated.
         * @since 1.7.1
         */
        do_action( "pre_oc_push_{$this->contenttype}_update", $push_response );

        $this->oc->object_cache()->update($this->uuid);

        /**
         * @deprecated since 1.7.1
         */
        if($this->contenttype === 'article') {
            /**
             * @deprecated since 1.7.1
             */
            do_action( 'oc_push_update', $push_response );
        }
    
        /**
         * Fires after the push has been updated.
         * @since 1.7.1
         */
        do_action( "oc_push_{$this->contenttype}_update", $push_response );
    }

    private function on_oc_add() {
        $push_response = $this->get_push_response();
        
        /**
         * Fires before the push has been added.
         * @since 1.7.1
         */
        do_action( "pre_oc_push_{$this->contenttype}_add", $push_response );

        /**
         * @deprecated since 1.7.1
         */
        do_action( 'oc_push_add', $push_response );

        /**
         * Fires after the push has been added.
         * @since 1.7.1
         */
        do_action( "oc_push_{$this->contenttype}_add", $push_response );
    }

	private function on_oc_delete() {
        $push_response = $this->get_push_response();

		//delete post
		$post = $this->get_wp_post( $this->uuid );
		if ( !empty( $post ) && isset( $post[0], $post[0]->ID ) && $post[0] !== null ) {
			$post_uuid = get_post_meta( $post[0]->ID, 'oc_uuid', true );
			if( $post_uuid === $this->uuid ) {

                /**
                 * Fires before the push has been deleted.
                 * @since 1.7.1
                 */
                do_action( "pre_oc_push_{$this->contenttype}_delete", $push_response );

				wp_delete_post( $post[0]->ID );
			}
		}

        $this->oc->object_cache()->delete($this->uuid);

		delete_transient( 'pe_' . $this->uuid );
		delete_transient( 'the_post_id_' . $this->uuid );
		delete_transient( 'the_post_' . $this->uuid );
  
		/**
         * @deprecated since 1.7.1
         */
		do_action( 'oc_push_delete', $push_response );
        
        /**
         * Fires after the push has been deleted.
         * @since 1.7.1
         */
        do_action( "oc_push_{$this->contenttype}_delete", $push_response );
	}

	private function get_object_data() {

        $properties = $this->oc->get_contenttype_properties( $this->raw_contenttype, [ 'boolean', 'date', 'double', 'integer', 'long', 'stream', 'string' ] );
        $filtered_properties = array_unique( (array)apply_filters( 'ew_notifier_update_' . $this->contenttype . '_properties', $properties ) );

        // If no difference in filtered properties, no need to fetch data from Open Content.
        if($properties === $filtered_properties) {
            return null;
        }

        $query_filter = apply_filters( 'ew_notifier_update_' . $this->contenttype . '_filter', '' );
        $object_data = $this->oc->get_single_object_data( $this->uuid, $filtered_properties, false, $query_filter );
        return $object_data;
    }

    private function get_push_response() {
        return array( 'eventtype' => $this->eventtype, 'uuid' => $this->uuid, 'response' => $this->raw_response );
    }

    private function get_wp_post( $uuid ) {
	    $article_arr    = [];
        $article_arr[0] = OcUtilities::get_article_post_by_uuid( $uuid );
        return $article_arr;
    }
}
