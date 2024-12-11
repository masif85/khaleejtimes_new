<?php
/*
 * This class keeps track of stats for the everyware install.
 */
class EveryStats {

	const EVERY_STATS_COUNT_KEY = 'every_stats_view_count';

	function __construct( $file ) {
		$env_settings = new EnvSettings();
		if( $env_settings->get_use_everystats() ) {
			add_action( 'wp_enqueue_scripts', array( &$this, 'every_stats_enqueue_scripts' ) );
			add_action( 'wp_ajax_every_stats_update_count', array( &$this, 'ajax_every_stats_update_count' ) );
			add_action( 'wp_ajax_nopriv_every_stats_update_count', array( &$this, 'ajax_every_stats_update_count' ) );
		}
	}

	/**
	 * Enqueue scripts for stat tracking.
	 */
	function every_stats_enqueue_scripts() {
		
		if( is_singular( 'article' ) ) { // If it is a single article, enqueue the script.
			global $post;			
			wp_enqueue_script( 'every_stats', plugin_dir_url( __FILE__ ) . '../assets/js/every_stats.js', array( 'jquery' ), '1.0', true );
			wp_localize_script( 'every_stats', 'ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'post_id' => $post->ID ) );
		}
	}

	/**
	 * AJAX Function: Update the stats counter for given object.
	 */
	function ajax_every_stats_update_count() {
		
		$post_id = isset( $_POST['post_id'] ) ?  wp_filter_nohtml_kses( trim( $_POST['post_id'] ) ) : null;
		if( $post_id !== null ) {

    		$count = get_post_meta( $post_id, self::EVERY_STATS_COUNT_KEY, true );
    		if( $count === '' ) {
        		$count = 1;
        		delete_post_meta( $post_id, self::EVERY_STATS_COUNT_KEY );
        		add_post_meta( $post_id, self::EVERY_STATS_COUNT_KEY, '0' );
    		}
    		else {
        		$count++;
        		update_post_meta( $post_id, self::EVERY_STATS_COUNT_KEY, $count );
    		}
		}
		die();
	}

	/**
	 * Queries the database for most read articles, if dates are given it gets them for the given range.
	 * @param  int $limit	Number of articles to return.
	 * @param  date string $start_date Start date of range.
	 * @param  date string $stop_date  Stop date of range.
	 * @return array Array with OC Article objects.
	 */
	static function every_stats_get_most_read_articles( $limit, $start_date = null, $stop_date = null ) {

		// Get the articles ordered by view count.
		$args = array(
			'post_type'  		=> 'article',
			'posts_per_page' 	=> $limit,
			'meta_key' 			=> self::EVERY_STATS_COUNT_KEY,
			'orderby' 			=> 'meta_value_num',
			'order' 			=> 'DESC',
			'inclusive' 		=> true
		);

		if( $start_date !== null && $stop_date !== null ) {

			$start_time = strtotime( $start_date );
			$stop_time	= strtotime( $stop_date );

			if( $start_time !== false && $stop_time !== false) {

				$args['date_query'] = array(
					array(
						'after'     => array(
							'year'  => date( 'Y', $start_time ),
							'month' => date( 'm', $start_time ),
							'day'   => date( 'd', $start_time ),
						),
						'before'    => array(
							'year'  => date( 'Y', $stop_time ),
							'month' => date( 'm', $stop_time ),
							'day'   => date( 'd', $stop_time ),
						),
					),
				);
			}
		}

        #TODO: Better key
        $cat_ttl = 3600;
        $cache_key = md5( "esgrma_" . $limit . $start_date . $stop_date );

        if ( false === ( $article_list = get_transient( $cache_key ) ) ) {
            $article_list = get_posts( $args );
            set_transient( $cache_key, $article_list, $cat_ttl );
        }

		#$article_list	= get_posts( $args );
		$article_arr	= array();
		$oc_api 		= new OcAPI();

		foreach( $article_list as $article ) {

            $cat_ttl = 3600;
            $cache_key = md5( "get_post_meta_" . $article->ID );

            if ( false === ( $uuid = get_transient( $cache_key ) ) ) {
                $uuid = get_post_meta( $article->ID, 'oc_uuid', true );
                set_transient( $cache_key, $uuid, $cat_ttl );
            }
			#$uuid = get_post_meta( $article->ID, 'oc_uuid', true );

			if( isset( $uuid ) ) {
				
				$article_data   = $oc_api->get_single_article( $uuid );
				if( isset( $article_data['article'] ) ) {
					array_push( $article_arr, $article_data['article'] );
				}
			}
		}

		return $article_arr;
	}
}