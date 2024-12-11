<?php

/**
 * @property array category
 * @property array contenttype
 * @property array dateline
 * @property array headline
 * @property array imageuuid
 * @property array leadin
 * @property array mobile_headline
 * @property array pubdate
 * @property array section
 * @property array tablet_headline
 * @property array text
 * @property array updated
 * @property array uuid
 * @property array web_headline
 */
class OcArticle extends AbstractOcObject {
    
    /**
     * @var bool
     */
	private $wp_post_article;
    
    /**
     * OcArticle constructor.
     */
	public function __construct() {
		$this->wp_post_article = false;
	}
    
    /**
     * Public function to strip tags from text
     *
     * @scope public
     *
     * @param string - name of OC-text prop
     *
     * @return string with stripped tags
     */
	public function get_normalized_text( $name ) {

		$text = $this->$name;
		$text = str_replace( 'subheadline1', 'h4', $text[ 0 ] );
		$text = preg_replace( '/<\/?body>/', '', $text );

		return $text;
	}
    
    /**
     * Public function to strip tags from multi value props
     *
     * @scope public
     *
     * @param string - name of OC-prop to clean
     *
     * @return array with clean props
     */
	public function get_cleaned_multi_value_prop( $name ) {
		$body = array();

		if ( isset( $this->$name ) ) {
			foreach ( $this->$name as $value ) {
				$body[] = strip_tags( $value, '<p><a>' );
			}
		}

		return $body;
	}
    
    /**
     * Public helper function to get content, instead of array, from prop
     *
     * @param  string property name
     *
     * @return string property content or empty
     */
	public function get_value( $name ) {
		$name = strtolower( $name );
		if ( isset( $this->prop_data[$name][0] ) ) {
			return $this->prop_data[$name][0];
		}
        return '';
	}
    
    /**
     * Public function to get "The post" wp object
     *
     * @return WP_Post object
     */
	public function get_post(){

		$article = $this->wp_post_article;

		if ( !$article ) {
			$article = OcUtilities::get_article_post_by_uuid( $this->uuid[0] );
			$this->wp_post_article = $article;
		}

		return $article;
	}
    
    /**
     * Public function to get article permalink
     *
     * @scope public
     * @return string article permalink
     */
	public function get_permalink() {
		$article = $this->get_post();
		return !$article ? '' : get_permalink( $article->ID );
	}
    
    /**
     * Public function to get article headline for the device used accessing it.
     *
     * @scope public
     * @return string headline
     */
	public function get_device_headline() {
		$device_headline	= '';
        
        if( isset( $this->mobile_headline[ 0 ] ) && $this->mobile_headline[ 0 ] !== '' && OcUtilities::is_mobile() && ! OcUtilities::is_tablet() ) {
            $device_headline = $this->mobile_headline[ 0 ];
        } else if( isset( $this->tablet_headline[ 0 ] ) && $this->tablet_headline[ 0 ] !== '' && OcUtilities::is_tablet() ) {
            $device_headline = $this->tablet_headline[ 0 ];
        } else if( isset( $this->web_headline[ 0 ] ) && $this->web_headline[ 0 ] !== '' ) {
            $device_headline = $this->web_headline[ 0 ];
        } else if( isset( $this->headline[ 0 ] ) && $this->headline[ 0 ] !== '' ) {
            $device_headline = $this->headline[ 0 ];
        }
        
        return $device_headline;
	}
    
    /**
     * Public function to get article comment count
     *
     * @scope public
     * @return int article comment count
     */
	public function get_comment_count() {
        $article = $this->get_post();
        $key = md5( 'article_ccount_' . $article->ID );
        
        if( false === ( $comments = get_transient( $key ) ) ) {
        	$comments = wp_count_comments( $article->ID );
        	set_transient( $key, $comments, 60 * 30 );
        }
        
        return $comments->total_comments;
    }
    
    /**
     * Public function to get article comment link
     *
     * @scope public
     * @return string article comment link
     */
    public function get_comment_link() {

        $article = $this->get_post();
        return get_comments_link( $article->ID );
    }
    
    /**
     * Public function to get the view count for the article
     *
     * @scope public
     * @return int article view count
     */
    public function get_view_count() {
        
        $article = $this->get_post();
        $count   = get_post_meta( $article->ID, EveryStats::EVERY_STATS_COUNT_KEY, true );
        if( $count === '' ) {
            return 0;
        }
        
        return $count;
    }
    
    /**
     * Function to add mapped properties to the article
     *
     * @since 1.4.2
     * @return void
     */
    
    public function set_mapped_properties() {
        // First add mapped property to map then add it to the article
        $this->fill( array_map( function ( $prop ) {
            return $this->get( $prop );
        }, OcUtilities::get_property_map() ) );
    }
}