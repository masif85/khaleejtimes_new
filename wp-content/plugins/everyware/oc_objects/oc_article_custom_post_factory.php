<?php

/*
 * This class is responsible for creating a custom post type of an Article object
 * Code moved here to prevent unwanted use of this function directly from an OcArticle object
 * This function is being used from OcConnection object, get_single_article
 */

class OcArticleCustomPostFactory {

	CONST WEB_HEADLINE = 'web_headline';
	CONST TABLET_HEADLINE = 'tablet_headline';
	CONST MOBILE_HEADLINE = 'mobile_headline';

	private $article;

	public function __construct( OcArticle $article ) {
		$this->article = $article;
		$this->create_oc_article_posttype();
	}

	/*
	 * Public function to generate an custom post type object of each article
	 * Previously done by __construct but since dynamical props where implemented
	 * this function must be called after instantiation of an OcArticle object to
	 * guarantee that all props have been set
	 *
	 * @scope - public
	 * @param -
	 * @return -
	 */
	public function create_oc_article_posttype() {
        $trans_key = 'pe_' . $this->article->uuid[0];

        if( 'true' === get_transient($trans_key) ) {
            return;
        }

        set_transient($trans_key, 'true', 3600);
        $article_check = OcUtilities::get_article_post_id_by_uuid($this->article->uuid[0]);

        if($article_check == null) {
			// If tags is an array convert it to a comma separated string
			$wp_tags = $this->article->tags;
			if ( is_array( $this->article->tags ) ) {
                $wp_tags = implode( ",", array_map(
                        function ($tag) {
                            //Only add strings, ignore concept objects
                            if(is_string($tag)){
                                return $tag;
                            }
                        },
                        $this->article->tags)
                );
			}

            // Temporarily switch to local timezone.
            if ( get_option( 'timezone_string' ) ) {
                $original_date_default_timezone = date_default_timezone_get();
                date_default_timezone_set( get_option( 'timezone_string' ) );
            }

            $post_date     = $this->get_article_post_date();
            $post_time     = strtotime($post_date);
            $post_date     = date('Y-m-d H:i:s', $post_time);
            $post_date_gmt = gmdate('Y-m-d H:i:s', $post_time);

            $post_status = $this->get_article_post_status();

            /** @todo Uncomment this block once Twig (and other components that calculate dates) get their timezone
             *        from Wordpress instead of PHP.
             */
//            // Restore timezone if we changed it.
//            if (isset($original_date_default_timezone)) {
//                date_default_timezone_set($original_date_default_timezone);
//            }

			// Set data for custom post type Article
			$article                 	= array();
			$article['post_type']    	= 'article';
			$article['post_content'] 	= '';
			$article['post_parent']  	= 0;
			$article['post_status']  	= $post_status;
			$article['post_title']   	= $this->generate_oc_article_slug();
			$article['post_name']    	= $article['post_title'];
			$article['tags_input']   	= $wp_tags;
			$article['post_date']		= $post_date;
			$article['post_date_gmt']	= $post_date_gmt;
			$article['post_category']   = CustomOcPostType::add_sanitized_article_categories( $this->article );

	        // Create the page and set it's template
			$post_id = wp_insert_post( $article );


			if ( $post_id !== 0 ) {
                set_transient($trans_key, 'true', 3600);
				$post_id = absint( $post_id );
				update_post_meta( $post_id, '_wp_page_template', 'single-article.php' );
				add_post_meta( $post_id, 'oc_uuid', $this->article->uuid[0], $unique = true );
				add_post_meta( $post_id, '_customize_sidebars', 'yes' );
                add_post_meta( $post_id, 'url_term_order', $article['post_category'], true);
				add_post_meta( $post_id, EveryStats::EVERY_STATS_COUNT_KEY, 0, true );

                do_action( 'every_article_created', $post_id, $this->article->uuid[0] );
			} else {
                set_transient($trans_key, 'false', 3600);
            }
		} else {
            set_transient($trans_key, 'true', 3600);
        }
	}

	/*
	 * Private function that generates a slug for the given article object.
	 * @scope - private
	 * @param OcArticle $article
	 * @return string
	 */
	private function generate_oc_article_slug() {
//		$oc              = new OpenContent();
		$oc              = OpenContent::getInstance();
		$slug_properties = $oc->getSlugProperties();
		$slug_max_length = $oc->getSlugMaxLength();
		$slug            = '';

		if ( isset( $slug_properties ) ) {
			foreach ( $slug_properties as $property ) {
				$property = strtolower( $property );
				if ( isset( $this->article->$property ) && $this->article->$property !== '' ) {
					$value = $this->article->$property;
					if ( isset( $value[0] ) && $value[0] !== '' ) {

						$slug = $value[0];
						$slug = strip_tags( $value[0] );
						$slug = mb_substr( $slug, 0, $slug_max_length );

						// Clean unwanted characters from slug.
						$slug = preg_replace("/\x{2009}/iu", "", $slug); // Weird space from NP.
						$slug = preg_replace("/\x{e009}/u", "", $slug); // Weird line break from NP.
						$slug = preg_replace("/\x{0085}/u", "", $slug); // Weird line break from NP.
						$slug = preg_replace("/\x{2028}/u", "", $slug); // Weird line break from NP.
						$slug = preg_replace("/\x{2029}/u", "", $slug); // Weird line break from NP.

						break;
					}
				}
			}
		}

		return $slug;
	}

    /**
     * @return string
     */
    private function get_article_post_date()
    {
        if (isset($this->article->pubdate[0])) {
            return $this->article->pubdate[0];
        }

        if (isset($this->article->created[0])) {
            return $this->article->created[0];
        }

        return date('Y-m-d');
    }

    /**
     * @return string
     */
    private function get_article_post_status()
    {
        $env_settings = new EnvSettings();
        $hide_future  = $env_settings->get_pubdate_hide_property();

        if ($hide_future && isset($this->article->pubdate[0])) { // If articles with pubdates in the future should be hidden.
            $pubdate_timestamp = strtotime($this->article->pubdate[0]);
            $now_timestamp     = time();

            if ($pubdate_timestamp > $now_timestamp) {
                return 'future';
            }
        }

        return 'publish';
    }
}