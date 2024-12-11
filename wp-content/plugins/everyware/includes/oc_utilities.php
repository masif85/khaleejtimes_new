<?php
/**
 * OcUtilities class
 *
 * Designed to make it easy to render OC material to the page
 *
 * Will render Device specific material were possible
 *
 * @author Infomaker Scandinavie AB
 *
 */

/*
|--------------------------------------------------------------------------
| Utility class filled with Static functions
| Designed to make it easy to render OC material to the page
| Will render Device specific material were possible
|--------------------------------------------------------------------------
*/
class OcUtilities {

    const IS_MOBILE = 'is_mobile';
    const IS_TABLET = 'is_tablet';

    private static $device;
    private static $device_map = array( self::IS_MOBILE => null, self::IS_TABLET => null );
    
    /**
     * Contains a list of properties mapped by the user
     *
     * @var array
     */
    private static $property_map;
    
    /**
     * @since 1.4.2
     * @return array
     */
    public static function get_property_map() {
        if( self::$property_map === null) {
            $pm = new PropertyMap();
            self::$property_map = $pm->get_article_property_map();
        }

        return self::$property_map;
    }

    public static function is_mobile() {
        if( is_null( self::$device_map[ self::IS_MOBILE ] ) ) {
            self::$device_map[ self::IS_MOBILE ] = isset( $_SERVER['EVERY_DEVICE'] ) && $_SERVER['EVERY_DEVICE'] === 'mobile' ? true : false;
        }

        return self::$device_map[ self::IS_MOBILE ];
    }

    public static function is_tablet() {
        if( is_null( self::$device_map[ self::IS_TABLET ] ) ) {
            self::$device_map[ self::IS_TABLET ] = isset( $_SERVER['EVERY_DEVICE'] ) && $_SERVER['EVERY_DEVICE'] === 'tablet' ? true : false;
        }

        return self::$device_map[ self::IS_TABLET ];
    }

	public static function get_site_shortname() {

		$key = 'site_shortname';
		if( false === ( $site_shortname = get_transient( $key ) ) ) {

			$site_shortname = get_bloginfo( 'url' );
			$site_shortname = str_replace( 'http://', '', $site_shortname );
			set_transient( $key, $site_shortname, 3600 );
		}

		return $site_shortname;
	}

	public static function render_np_softcrop_image( $imageuuid, $intended_width, $intended_height, $opt_base_url = '' ){
		$np_height  = '';
		$np_widht   = '';
		$np_start_x = '';
		$np_start_y = '';
		$np_zoom    = '';

		if ( isset( $imageuuid ) ) {
			if( !isset( $opt_base_url ) || $opt_base_url === '' ){
				trigger_error("Calling get_np_softcrop_image without imengine base url is deprecated.", E_USER_WARNING);
			}

			$ideal     = $intended_width / $intended_height;
			$best_diff = 'not set';
			$match_key = null;
			$oc_api    = new OcAPI();
			$include   = array(
				'crop_height',
				'crop_width',
				'crop_posx',
				'crop_posy',
				'crop_zoom'
			);

            $intended_diff_w = ($intended_width / $intended_height);
            $intended_diff_h = ($intended_height / $intended_width);

			$image_meta = $oc_api->get_single_image_metadata( $imageuuid, $include );

			if ( is_object( $image_meta ) && get_class( $image_meta ) === 'OcImage' && isset( $image_meta->crop_height[0] ) ) {
				foreach ($image_meta->crop_height as $key => $value) {

                    $zoom_num = 100-intval($image_meta->crop_zoom[ $key ]);
                    $zoom_num = $zoom_num == 0 ? 1 : $zoom_num;

					if ( $image_meta->crop_width[ $key ] >= $intended_width && $image_meta->crop_height[ $key ] >= $intended_height ) {
                        $diff = ($ideal / ( $image_meta->crop_width[ $key ] / $image_meta->crop_height[ $key ] ) );

						$diff_w = abs($intended_diff_w - ( $image_meta->crop_width[ $key ] / $image_meta->crop_height[ $key ] ));
                        $diff_h = abs($intended_diff_h - ( $image_meta->crop_height[ $key ] / $image_meta->crop_width[ $key ] ));
                        $diff = $diff_w + $diff_h;

						if ( $best_diff === 'not set' ) {
							$best_diff = $diff;
							$match_key = $key;
						} else {
							if ( $diff <= $best_diff ) {
								$best_diff = $diff;
								$match_key = $key;
							}
						}
					}
				}
			}

			//If we have a match, render it!
			if ( isset( $match_key ) ) {
				$np_height  = $image_meta->crop_height[ $match_key ];
				$np_widht   = $image_meta->crop_width[ $match_key ];
				$np_start_x = $image_meta->crop_posx[ $match_key ];
				$np_start_y = $image_meta->crop_posy[ $match_key ];
				$np_zoom    = trim( floatval( $image_meta->crop_zoom[ $match_key ] ) );
                $zoom = 0;

                if(abs($np_widht-$intended_width) < abs($np_height-$intended_height)) {
                    $zoom = $intended_width/$np_widht;
                } else {
                    $zoom = $intended_height/$np_height;
                }

                print '<img src="' . $opt_base_url . '?uuid=' . $imageuuid . '&type=preview&source=false&function=np_crop&width=' . $intended_width . '&height=' . $intended_height . '&x=' . $np_start_x . '&y=' . $np_start_y . '&z='. $zoom .'" />';

			//Else we render image as is, using thumbnail function for good quality
			}else{
				print '<img src="' . $opt_base_url . '?uuid=' . $imageuuid . '&type=preview&source=false&function=thumbnail&width=' . $intended_width . '&height=' . $intended_height . '" />';
			}
		}
	}


    /**
     * Returns a url as string to an image
     * @param $imageuuid
     * @param $intended_width
     * @param $intended_height
     * @param string $opt_base_url
     * @return string url to the image
     */
    public function get_np_softcrop_image( $imageuuid, $intended_width, $intended_height, $opt_base_url = '')
    {
        if ( isset( $imageuuid ) ) {
            if( !isset( $opt_base_url ) || $opt_base_url === '' ){
                trigger_error("Calling get_np_softcrop_image without imengine base url is deprecated.", E_USER_WARNING);
            }

            $ideal     = $intended_width / $intended_height;
            $best_diff = 'not set';
            $match_key = null;
            $oc_api    = new OcAPI();
            $include   = array(
                'crop_height',
                'crop_width',
                'crop_posx',
                'crop_posy',
                'crop_zoom',
                'Filename'
            );

            $intended_diff_w = ($intended_width / $intended_height);
            $intended_diff_h = ($intended_height / $intended_width);

            $image_meta = $oc_api->get_single_image_metadata( $imageuuid, $include );
            $filename = $image_meta->filename[0];

            if ( is_object( $image_meta ) && get_class( $image_meta ) === 'OcImage' && isset( $image_meta->crop_height[0] ) ) {
                foreach ($image_meta->crop_height as $key => $value) {

                    $zoom_num = 100-intval($image_meta->crop_zoom[ $key ]);
                    $zoom_num = $zoom_num == 0 ? 1 : $zoom_num;

                    if ( $image_meta->crop_width[ $key ] >= $intended_width && $image_meta->crop_height[ $key ] >= $intended_height ) {
                        $diff = ($ideal / ( $image_meta->crop_width[ $key ] / $image_meta->crop_height[ $key ] ) );

                        $diff_w = abs($intended_diff_w - ( $image_meta->crop_width[ $key ] / $image_meta->crop_height[ $key ] ));
                        $diff_h = abs($intended_diff_h - ( $image_meta->crop_height[ $key ] / $image_meta->crop_width[ $key ] ));
                        $diff = $diff_w + $diff_h;

                        if ( $best_diff === 'not set' ) {
                            $best_diff = $diff;
                            $match_key = $key;
                        } else {
                            if ( $diff <= $best_diff ) {
                                $best_diff = $diff;
                                $match_key = $key;
                            }
                        }
                    }
                }
            }

            //If we have a match, render it!
            if ( isset( $match_key ) ) {
                $np_height  = $image_meta->crop_height[ $match_key ];
                $np_widht   = $image_meta->crop_width[ $match_key ];
                $np_start_x = $image_meta->crop_posx[ $match_key ];
                $np_start_y = $image_meta->crop_posy[ $match_key ];
                $np_zoom    = trim( floatval( $image_meta->crop_zoom[ $match_key ] ) );
                $zoom = 0;

                if(abs($np_widht-$intended_width) < abs($np_height-$intended_height)) {
                    $zoom = $intended_width/$np_widht;
                } else {
                    $zoom = $intended_height/$np_height;
                }

                return $opt_base_url . '?uuid=' . $imageuuid . '&type=preview&source=false&function=np_crop&width=' . $intended_width . '&height=' . $intended_height . '&x=' . $np_start_x . '&y=' . $np_start_y . '&z='. $zoom;

                //Else we render image as is, using thumbnail function for good quality
            }else{
                return $opt_base_url . '?uuid=' . $imageuuid . '&type=preview&source=false&function=thumbnail&width=' . $intended_width . '&height=' . $intended_height.'&q=90';
            }
        }
    }


    public function get_np_softcrop_image_url( $imageuuid, $intended_width, $intended_height, $opt_base_url = '')
    {
        if ( isset( $imageuuid ) ) {
            if( !isset( $opt_base_url ) || $opt_base_url === '' ){
                trigger_error("Calling get_np_softcrop_image without imengine base url is deprecated.", E_USER_WARNING);
            }

            $ideal     = $intended_width / $intended_height;
            $best_diff = 'not set';
            $match_key = null;
            $oc_api    = new OcAPI();
            $include   = array(
                'crop_height',
                'crop_width',
                'crop_posx',
                'crop_posy',
                'crop_zoom',
                'Filename'
            );

            $intended_diff_w = ($intended_width / $intended_height);
            $intended_diff_h = ($intended_height / $intended_width);

            $image_meta = $oc_api->get_single_image_metadata( $imageuuid, $include );
            $filename = $image_meta->filename[0];

            if ( is_object( $image_meta ) && get_class( $image_meta ) === 'OcImage' && isset( $image_meta->crop_height[0] ) ) {
                foreach ($image_meta->crop_height as $key => $value) {

                    $zoom_num = 100-intval($image_meta->crop_zoom[ $key ]);
                    $zoom_num = $zoom_num == 0 ? 1 : $zoom_num;

                    if ( $image_meta->crop_width[ $key ] >= $intended_width && $image_meta->crop_height[ $key ] >= $intended_height ) {
                        $diff = ($ideal / ( $image_meta->crop_width[ $key ] / $image_meta->crop_height[ $key ] ) );

                        $diff_w = abs($intended_diff_w - ( $image_meta->crop_width[ $key ] / $image_meta->crop_height[ $key ] ));
                        $diff_h = abs($intended_diff_h - ( $image_meta->crop_height[ $key ] / $image_meta->crop_width[ $key ] ));
                        $diff = $diff_w + $diff_h;

                        if ( $best_diff === 'not set' ) {
                            $best_diff = $diff;
                            $match_key = $key;
                        } else {
                            if ( $diff <= $best_diff ) {
                                $best_diff = $diff;
                                $match_key = $key;
                            }
                        }
                    }
                }
            }

            //If we have a match, render it!
            if ( isset( $match_key ) ) {
                $np_height  = $image_meta->crop_height[ $match_key ];
                $np_widht   = $image_meta->crop_width[ $match_key ];
                $np_start_x = $image_meta->crop_posx[ $match_key ];
                $np_start_y = $image_meta->crop_posy[ $match_key ];
                $np_zoom    = trim( floatval( $image_meta->crop_zoom[ $match_key ] ) );
                $zoom = 0;

                if(abs($np_widht-$intended_width) < abs($np_height-$intended_height)) {
                    $zoom = $intended_width/$np_widht;
                } else {
                    $zoom = $intended_height/$np_height;
                }
//                return $opt_base_url . $filename .'/uuid_' . $imageuuid . '_type_preview_source_false_f_np+crop_w_' . $intended_width . '_h_' . $intended_height.'_q_100'. '_x_' . $np_start_x . '_y_' . $np_start_y . '_z_'. $zoom;
                return $opt_base_url . '?uuid=' . $imageuuid . '&type=preview&source=false&function=np_crop&width=' . $intended_width . '&height=' . $intended_height . '&x=' . $np_start_x . '&y=' . $np_start_y . '&z='. $zoom;

                //Else we render image as is, using thumbnail function for good quality
            }else{
//                return $opt_base_url . $filename .'/uuid_' . $imageuuid . '_type_preview_source_false_f_thumbnail_w_' . $intended_width . '_h_' . $intended_height.'_q_100';
                return $opt_base_url . '?uuid=' . $imageuuid . '&type=preview&source=false&function=thumbnail&width=' . $intended_width . '&height=' . $intended_height.'&q=100';
            }
        }
    }



	/**
	 * Function to truncate text
	 *
	 * @scope public
	 *
	 * @param        $string
	 * @param        $limit
	 * @param int    $tolerance
	 * @param string $break
	 * @param string $pad
	 *
	 * @return string
	 */
	public static function truncate_article_text( $string, $limit, $tolerance = 10, $break = ".", $pad = "." ) {
		$string   = strip_tags( $string, '<i><b>' );
		$original = $string;
		// return with no change if string is shorter than $limit
		if ( strlen( $string ) <= $limit ) return $string;

		$breakpoint = strpos( $string, $break, $limit );

		//Truncate string at closes $break (default set to dot (.))
		$string = mb_substr( $string, 0, $breakpoint ) . $pad;

		//If string is to long after truncation, just chop the original at given limit and add "..."
		if ( strlen( $string ) > $limit + $tolerance ) {
			$string = mb_substr( $string, 0, $limit );
			//$string = mb_substring($original, 0, $limit);
			$string = trim( $string ) . "...";
		}

		//If string is to short after truncation, just chop the original string at given limit and add "..."
		if ( strlen( $string ) < $limit - $tolerance ) {
			$string = mb_substr( $original, 0, $limit );
			//$string = mb_substring($original, 0, $limit);
			$string = trim( $string ) . "...";
		}

		return $string;
	}

	/**
	 * Function to generate Device specific Batcache key
	 *
	 * @scope public
	 *
	 * @return string
	 */
	public static function get_device_specific_cache_key() {
		if ( self::is_tablet() ) {
			return 'tablet';
		} elseif ( self::is_mobile() ) {
			return 'mobile';
		} else {
			return 'web';
		}
	}

	public static function get_article_post_id_by_uuid($uuid) {
		$article = null;

		if ( false === ( $article = get_transient( 'the_post_id_' . $uuid ) ) ) {

			$article = null;

			global $wpdb;
			$meta_table     = $wpdb->prefix . 'postmeta';
			$posts_table    = $wpdb->prefix . 'posts';
			$sql            = $wpdb->prepare( 'SELECT pm.post_id FROM ' . $meta_table . ' pm LEFT JOIN ' . $posts_table . ' 
			                                    p ON p.ID = pm.post_id WHERE pm.meta_key = %s AND pm.meta_value = %s AND p.post_type = %s;',
                                          'oc_uuid', $uuid, 'article' );
			$results        = $wpdb->get_results( $sql );

			if ( isset( $results[0]->post_id ) ) {
				$article = $results[0]->post_id;
				set_transient( 'the_post_id_' . $uuid, $article, 86400 );
			}
		}

		return $article;
	}

	public static function get_article_post_by_uuid($uuid) {
		$article = null;

		if ( false === ( $article = get_transient( 'the_post_' . $uuid ) ) ) {
			$article = null;

			$post_id = OcUtilities::get_article_post_id_by_uuid($uuid);
			if( isset( $post_id ) ) {

				$article = get_post( $post_id );
				if ( isset( $article ) && intval( $post_id ) === $article->ID ) {
					set_transient( 'the_post_' . $uuid, $article, 86400 );
				}
			}
		}

		return $article;
	}

	/**
	 * Get uuid by post id
	 *
	 * @scope public
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	public static function get_uuid_by_post_id($post_id) {
	  $cache_key = md5( "ew_get_post_meta_" . $post_id );

	  if ( false === ( $uuid = get_transient( $cache_key ) ) ) {
	      $uuid = get_post_meta( $post_id, 'oc_uuid', true );
	      if( !empty( $uuid ) ) {
	        set_transient( $cache_key, $uuid, 3600 );
	      }
	  }

	  return $uuid;
	}

	/**
	 * Function to get an articles permalink
	 *
	 * @scope public
	 *
	 * @param OcArticle $article
	 *
	 * @return mixed
	 */
	public static function get_article_permalink( $article ) {
		if ( is_object( $article ) ) {
			/*$args = array(
				'post_type'  => 'article',
				'posts_per_page' => 1,
				'meta_query' => array(
					array(
						'key'     => 'oc_uuid',
						'value'   => $article->uuid[0],
						'compare' => '='
					)
				) );

			$page_arr = get_posts( $args );
			$page     = $page_arr[0];*/
            $page = OcUtilities::get_article_post_by_uuid($article->uuid[0]);

			return get_permalink( $page->ID );
		}
	}

	/**
	 * Function to get Headline from WP custom post type Article
	 * Function is device specific
	 *
	 * @scope public
	 *
	 * @param OcArticle $article
	 *
	 * @return mixed
	 */
	public static function get_article_post_meta_headline( OcArticle $article ) {

        /*$args = array(
			'post_type'  => 'article',
			'posts_per_page' => 1,
			'meta_query' => array(
				array(
					'key'     => 'oc_uuid',
					'value'   => $article->uuid[0],
					'compare' => '='
				)
			) );

		$page_arr = get_posts( $args );
		$page     = $page_arr[0];*/
        $page = OcUtilities::get_article_post_by_uuid($article->uuid[0]);
		$headline = '';

		if ( self::is_tablet()) {

			$headline = get_post_meta( $page->ID, OcArticleCustomPostFactory::TABLET_HEADLINE, $single = true );

		} elseif ( self::is_mobile() ) {

			$headline = get_post_meta( $page->ID, OcArticleCustomPostFactory::MOBILE_HEADLINE, $single = true );

		} else {

			$headline = get_post_meta( $page->ID, OcArticleCustomPostFactory::WEB_HEADLINE, $single = true );

		}

		return $headline;
	}

    /**
     * Function to get a board by tag name.
     *
     * @param $tag
     * @return array
     */
    public static function get_board_by_tag( $tag ) {

        // TODO: Cache this stuff

        $args = array(
            'post_type'         => 'EveryBoard',
            'posts_per_page'    => 1,
            'orderby'           => 'post_date',
            'tag'               => $tag
        );

        return get_posts( $args );
    }

	/**
	 * Function to get search result from OC
	 * Will make Search result into a global Array
	 *
	 * @scope public
	 *
	 * @return array
	 */
	public static function get_search_result( $input_param = 'q', $_include_array = array(), $sort = '', $start= 0, $limit=25 ) {
		if ( isset( $_GET[$input_param] ) && $_GET[$input_param] !== "" ) {

			$search_text = wp_filter_nohtml_kses( trim( $_GET[$input_param] ) );

			if ( $search_text === '' ) {
				$search_text = '*:*';
			}

			/*if ( isset( $_GET['widget_extra_query'] ) ) {
				$search .= ' ' . $_GET['widget_extra_query'];
			}*/

			$include_array = array();
			if ( isset( $_GET['contenttype'] ) && $_GET['contenttype'] === OcSearch::ARTICLES ) {
				array_push( $include_array, OcSearch::ARTICLES );
			}

			if ( isset( $_GET['contenttype'] ) && $_GET['contenttype'] === OcSearch::IMAGES ) {
				array_push( $include_array, OcSearch::IMAGES );
			}

			if ( isset( $_GET['contenttype'] ) && $_GET['contenttype'] === OcSearch::ADS ) {
				array_push( $include_array, OcSearch::ADS );
			}

			if ( empty( $include_array ) && !empty( $_include_array ) ) {
				$include_array = $_include_array;
			}

			$oc_api             = new OcAPI();

			$sort 			= isset( $_GET['sort'] ) ? $_GET['sort'] : $sort;
			$facet_limit 	= isset( $_GET['facet_limit'] ) ? $_GET['facet_limit'] : null;
			$facet_mincount = isset( $_GET['facet_mincount'] ) ? $_GET['facet_mincount'] : null;

			$search_result_data = $oc_api->text_search( $search_text, $include_array, $sort, $facet_limit, $facet_mincount, $start, $limit );
		} else {
			$search_result_data = array();
		}
		$GLOBALS['oc_search_result'] = $search_result_data;
		return $search_result_data;
	}

	/**
	 * Function to display Page dateline
	 *
	 * @scope public
	 *
	 * @param OcArticle $article
	 */
	public static function render_page_dateline( $article ) {
		if ( isset( $article->article_pagedateline[0] ) ) {
			echo '<p class="page_dateline">' . $article->article_pagedateline[0] . '</p>';
		} elseif ( isset( $article->pagedateline[0] ) ) {
			echo '<p class="page_dateline">' . $article->pagedateline[0] . '</p>';
		} elseif ( isset( $article->dateline[0] ) ) {
			echo '<p class="page_dateline">' . $article->dateline[0] . '</p>';
		} elseif ( isset( $article->location[0] ) ) {
			echo '<p class="page_dateline">' . $article->location[0] . '</p>';
		}
	}

    /**
     * Function to get Page dateline
     *
     * @scope public
     *
     * @param OcArticle $article
     */
    public static function get_page_dateline( $article ) {
        if ( isset( $article->article_pagedateline[0] ) ) {
            return $article->article_pagedateline[0];
        } elseif ( isset( $article->pagedateline[0] ) ) {
            return $article->pagedateline[0];
        } elseif ( isset( $article->dateline[0] ) ) {
            return $article->dateline[0];
        } elseif ( isset( $article->location[0] ) ) {
            echo $article->location[0];
        }
    }

    /**
     * Function to display Article comment count
     *
     * @scope public
     *
     * @param OcArticle $article
     */
	public static function render_article_comment_count( OcArticle $article ) {

		if( isset( $article->uuid[0] ) ) {
			/*$args = array(
				'post_type'  => 'article',
				'posts_per_page' => 1,
				'meta_query' => array(
				array(
					'key'     => 'oc_uuid',
					'value'   => $article->uuid[0],
					'compare' => '='
				)
			) );

			$post_arr = get_posts( $args );
			$post     = $post_arr[0];*/
            $post = OcUtilities::get_article_post_by_uuid($article->uuid[0]);
			$comments = wp_count_comments( $post->ID );

			print '<div class="comment_count">';
				print '<a href="' . get_comments_link( $post->ID ) . '">' . $comments->total_comments . '</a>';
				print '<div class="comment_arrow"></div>';
			print '</div>';
		}
	}

    /**
     * Function to get Article comment count
     *
     * @scope public
     *
     * @param OcArticle $article
     */
    public static function get_article_comment_count( OcArticle $article ) {

        if( isset( $article->uuid[0] ) ) {
            /*$args = array(
                'post_type'  => 'article',
                'posts_per_page' => 1,
                'meta_query' => array(
                    array(
                        'key'     => 'oc_uuid',
                        'value'   => $article->uuid[0],
                        'compare' => '='
                    )
                ) );

            $post_arr = get_posts( $args );
            $post     = $post_arr[0];*/
            $post = OcUtilities::get_article_post_by_uuid($article->uuid[0]);
            $comments = wp_count_comments( $post->ID );

            return $comments->total_comments;
        }
    }

	/**
	 * Removes all json caches for given uuid
	 * @param $uuid
	 */
	public static function delete_article_from_cache( $uuid ) {

		$oc = new OcAPI();
		$oc->delete_transient_notifier_push( $uuid );

		delete_transient( 'pe_' . $uuid );
		delete_transient( 'the_post_id_' . $uuid );
		delete_transient( 'the_post_' . $uuid );
	}

	/**
	 * Function to render admin message
	 *
	 * @scope public
	 *
	 * @param $text
	 * @param $class
	 */
	public static function render_admin_message( $text, $class ) {
		print '<div id="message" class="' . $class . '">';
		print '<p>' . esc_html( $text ) . '</p>';
		print '</div>';
	}

	/**
	 * Function to render a "pretty" date
	 *
	 * @scope public
	 *
	 * @param int $a
	 * @param int $b
	 *
	 * @return string
	 */
	public static function render_pretty_date( $a = 0, $b = 0 ) {
		if ( ! is_numeric( $a ) ) $a = strtotime( $a );
		if ( ! is_numeric( $b ) ) $b = strtotime( $b );
		if ( $a == 0 ) $a = time();
		if ( $b == 0 ) $b = time();

		if ( $a == $b ) return " just now";

		$q     = "ago";
		$value = $b - $a;

		if ( $value == 1 ) return " 1 second $q";
		if ( $value < 60 ) return " $value seconds $q";

		$value /= 60;
		if ( round( $value ) == 1 ) return " 1 minute $q";
		if ( $value < 60 ) return " " . round( $value ) . " minutes $q";

		$value /= 60;
		if ( round( $value ) == 1 ) return " 1 hour $q";
		if ( $value < 24 ) return " " . round( $value ) . " hours $q";

		$value /= 24;
		if ( round( $value ) == 1 ) return " yesterday ";
		if ( $value < 31 ) return " " . round( $value ) . " days $q";

		$value /= 31;
		if ( round( $value ) == 1 ) return " 1 month $q";
		if ( $value < 12 ) return " " . round( $value ) . " months $q";

		$value /= 12;
		if ( round( $value ) == 1 ) return " 1 year $q";
		return " " . round( $value ) . " years $q";
	}

}
