<?php

class CustomAjaxHandler {

	private $oc_api;

	public function __construct() {
		$this->oc_api = new OcAPI();

		//Handle Ajax request to test oc query
		add_action( 'wp_ajax_test_oc_query', array( &$this, 'oc_ajax_query_test' ) );

		//Handle Ajax request to get content types
		add_action( 'wp_ajax_get_content_types', array( &$this, 'oc_ajax_get_content_types' ) );

		// Handle Ajax request to get suggest results
		add_action( 'wp_ajax_get_suggest', array( &$this, 'oc_ajax_get_suggest' ) );

        add_action( 'wp_ajax_oc_purge_article_cache', array( &$this, 'oc_purge_article_cache') );
	}

    function oc_purge_article_cache() {
        $uuid = $_POST['uuid'];
        $success = $this->oc_api->delete_transient_notifier_push( $uuid );
	    delete_transient( 'the_post_id_' . $uuid );
	    delete_transient( 'the_post_' . $uuid );
	    delete_transient( 'pe_' . $uuid );

        if( $success ) {
            OcCacheHelper::getInstance()->clean_up_cache_map( $uuid );
            print 1;
        } else {
            print 0;
        }
        die(1);
    }

	function oc_ajax_query_test() {
		// get the submitted parameters
		if ( isset( $_POST['query'] ) && $_POST['query'] !== "" ) {
			$query = stripcslashes( trim( $_POST['query'] ) );

			$start = absint( $_POST['oc_query_start'] );
			$limit = absint( $_POST['oc_query_limit'] );
			$sort  = $_POST['oc_query_sort'];

			$this->oc_api->prepare_query( $query, $start, $limit, $sort );
			$result = $this->oc_api->ajax_test_query();
		} else {
			$result = array( __( 'Error', 'every' ) => __( 'Empty or invalid query', 'every' ) );
		}

		//generate the response
		$response = json_encode( $result );

		// response output
		echo $response;

		// IMPORTANT: don't forget to "exit"
		exit;
	}

	function oc_ajax_get_content_types() {

		$result = $this->oc_api->ajax_get_content_types();
		echo json_encode( $result );

		exit;
	}

	function oc_ajax_get_suggest() {

		if ( isset( $_GET['field'] ) ) {

			header( 'Content-type: application/json' );
			$field                = $_GET['field'];
			$q                    = isset( $_GET['q'] ) ? $_GET['q'] : null;
			$incompleteWord       = isset( $_GET['incompleteWord'] ) ? $_GET['incompleteWord'] : null;
			$incompleteWordInText = isset( $_GET['incompleteWordInText'] ) ? $_GET['incompleteWordInText'] : null;

			$result = $this->oc_api->ajax_get_suggest( $field, $q, $incompleteWord, $incompleteWordInText );
			echo json_encode( $result );
		}

		die();
	}
}