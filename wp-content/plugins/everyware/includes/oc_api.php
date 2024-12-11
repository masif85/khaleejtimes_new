<?php

/**
 * DocBlock for OcApi class
 *
 * Wrapper class to create an open API towards Open Content
 *
 * @author Infomaker Scandinavie AB
 *
 */

//require_once( 'oc_connection.php' );
//require_once dirname( __FILE__ ) . '/../oc_objects/oc_ad.php';
//require_once dirname( __FILE__ ) . '/../oc_objects/oc_article.php';
//require_once dirname( __FILE__ ) . '/../oc_objects/oc_image.php';
//require_once dirname( __FILE__ ) . '/../oc_objects/oc_article_custom_post_factory.php';
//require_once dirname( __FILE__ ) . '/../oc_objects/oc_objects_collection.php';

/*
|--------------------------------------------------------------------------
| Open Content API Class
| Used to interact with Open Content
|--------------------------------------------------------------------------
*/

class OcAPI extends OcConnection {
    
    /**
     * @var OpenContent
     */
	private static $_oc;

	/**
	 * Public construct
	 * Will make a singleton Open Content object
	 *
	 * @scope public
	 */
	public function __construct() {
		self::$_oc = self::$_oc === null ? OpenContent::getInstance() : self::$_oc;
		parent::__construct( self::$_oc );
	}

	/**
	 * Function to prepare a query
	 * Will append a prepared query onto the OC URL
	 *
	 * @scope public
	 *
	 * @param string $query
	 * @param string $start
	 * @param string $limit
	 * @param string $sort
	 *
	 * @return $this
	 */
	public function prepare_query( $query, $start = '', $limit = '', $sort = '' ) {
		$this->oc_url = $this->prep_oc_query( $query, $start, $limit, $sort );
		return $this;
	}

	/**
	 * Function to prepare a Widgets oc query
	 *
	 * @scope public
	 *
	 * @param array $instance
	 *
	 * @return $this
	 */
	public function prepare_widget_query( array $instance = [] ) {
		$query = $instance['oc_query'] ?? null;
		$start = $instance['oc_query_start'] ?? null;
		$limit = $instance['oc_query_limit'] ?? null;
		$sort  = $this->validateSortOption($instance['oc_query_sort'] ?? '');

		$this->oc_url = $this->prep_oc_query( $query, $start, $limit, $sort );
		return $this;
	}

	/**
	 * Function to prepare a suggest query
	 *
	 * @scope public
	 *
	 * @param array $instance
     *
     * @return void
     */
	public function prepare_suggest_query( array $instance = [] ) {
		$suggest = isset( $instance['oc_suggest_field'] ) ? $instance['oc_suggest_field'] : null;
		$query   = isset( $instance['oc_query'] ) ? $instance['oc_query'] : null;

		$this->prepare_oc_suggest_query( $suggest, $query );
	}

	/**
	 * Function used to prepare a query before its sent to Event Notifier
	 *
	 * @scope public
	 *
	 * @param array $instance
	 *
	 * @return string
	 */
	public function prepare_notifier_query( array $instance = [] ) {
		$query = isset( $instance['oc_query'] ) ? $instance['oc_query'] : null;
		$start = isset( $instance['oc_query_start'] ) ? $instance['oc_query_start'] : null;
		$limit = isset( $instance['oc_query_limit'] ) ? $instance['oc_query_limit'] : null;
		$sort  = isset( $instance['oc_query_sort'] ) ? $instance['oc_query_sort'] : null;
		return $this->_build_query_string( $query, $start, $limit, $sort, $url_encoded = true );
	}

	/**
	 * Function to get OC Content types
	 *
	 * @scope public
	 *
	 * @return array
	 */
	public function get_content_types() {
		return $this->ajax_get_content_types();
	}
    
    /**
     * Function to get the properties that will be sent for if none is specified
     *
     * @since 1.0.0
     * @return array
     */
    public function get_default_properties() {
        return $this->get_search_properties();
	}
    
    /**
     * Fetch property names from OC, filter by contenttype and data type.
     *
     * @param string $contenttype
     * @param string $type
     *
     * @return array
     */
    public function get_properties_by_type( $contenttype, $type ) {

        $return_array = [];

        try {
            $contenttypes = $this->get_contenttypes();
        } catch(RuntimeException $e){
            return $return_array;
        }

        foreach ( $contenttypes as $content_type ) {

            if( $content_type->name === $contenttype ) {
                foreach ( $content_type->properties as $property ) {

                    if( $type === null || $property->type === $type ) {
                        $return_array[] = $property->name;
                    }
                }
            }
        }

        return $return_array;
    }
    
    /**
     * Function to make a raw unfiltered query against Open Content
     *
     * @param  String $query
     * @param  string $start
     * @param  string $limit
     * @param  string $sort
     *
     * @return bool|null|string [JSON] Result form Open Content
     */
	public function get_raw_query( $query, $start = '', $limit = '', $sort = '' ){
		$this->oc_url = $this->prep_oc_query( $query, $start, $limit, $sort );

		return $this->get_json_result();
	}
    
    /**
     * Function to get full OC Query url data
     *
     * @scope public
     *
     * @param string $oc_query_url
     *
     * @return null|string
     */
	public function get_url_content( $oc_query_url ) {
		return $this->_get_remote_data( $oc_query_url );
	}

    /**
     * Get last used query.
     *
     * @return null|string
     */
    public function get_active_query() {
        return $this->oc_url;
    }
}
