<?php

class CustomOcPostType {
    
    /**
     * CustomOcPostType constructor.
     *
     * @param string $file
     * @since 1.0.7
     */
    public function __construct( $file ) {
        //Add hook to register OC custom post type
        add_action( 'init', [ &$this, 'copt_article_register_post_types' ] );
        
        //Add Hook to filter Custom post template on admin page
        add_filter( 'post_type_link', [ &$this, 'copt_custom_post_link' ], 10, 2 );
        
        add_filter( 'post_row_actions', [ &$this, 'copt_add_row_action' ], 10, 2 );
    }
    
    /*
     * Function to setup args for and register OC article as custom post type
     *
     * @since 1.0.7
     * @return void
     */
    public function copt_article_register_post_types() {
       
        $oc = OpenContent::getInstance();
        $postTypeSlug = $oc->getCustomPostTypeSlug();

        if ('%categoryname%' === $postTypeSlug) {
            add_rewrite_tag($postTypeSlug, '([^&]+)/([^/]+)?(:/([0-9]+))');
        }

        $article_args = [
            'public'        => true,
            'query_var'     => true,
            'menu_position' => 20,
            'menu_icon'     => EVERY_BASE . 'admin-style/images/article.png',
            'rewrite'       => [
                'slug'       => $postTypeSlug,
                'with_front' => false
            ],
            'supports'      => [
                'title',
                'custom-fields'
            ],
            'labels'        => [
                'name'               => __( 'Articles', 'every' ),
                'singular_name'      => __( 'Article', 'every' ),
                'add_new'            => __( 'Add New Article', 'every' ),
                'add_new_item'       => __( 'Add New Article', 'every' ),
                'edit_item'          => __( 'Edit Article', 'every' ),
                'new_item'           => __( 'New Article', 'every' ),
                'view_item'          => __( 'View Article', 'every' ),
                'search_items'       => __( 'Search Articles', 'every' ),
                'not_found'          => __( 'No Articles Found', 'every' ),
                'not_found_in_trash' => __( 'No Articles Found In Trash', 'every' )
            ],
            'taxonomies'    => [ 'category', 'post_tag' ]
        ];

        register_post_type( 'article', $article_args );
        add_action( 'generate_rewrite_rules', function ( $wp_rewrite ) {
            $rules                          = $wp_rewrite->rules;
            $rules[ '[^/]+/([^/]+)/?$' ]    = 'index.php?article=$matches[1]';
            $rules[ '([^/]+/)*([^/]+)/?$' ] = 'index.php?article=$matches[2]';
            $wp_rewrite->rules              = $rules;
        } );
    }

    /**
     * @param array $actions
     *
     * @since 1.0.7
     * @return array
     */
    public function copt_add_row_action( $actions ) {
        global $post;
        
        $uuid               = get_post_meta( $post->ID, 'oc_uuid', true );
        $actions[ 'purge' ] = "<a data-uuid='" . $uuid . "' class='purge_cache' href='#' class='purge_cache' title='" . __( 'Empty cache from this article', 'every' ) . "'>" . __( 'Purge Cache', 'every' ) . "</a>";
        
        return $actions;
    }
    
    /**
     * Fix custom post type permalinks
     *
     * @param $post_link
     * @param $post
     *
     * @since 1.0.7
     * @return string
     */
    public function copt_custom_post_link( $post_link, $post ) {
        
        $cat_ttl = 86400;
        
        if( ! is_object( $post ) || $post->post_type !== 'article' ) {
            return $post_link;
        }
        
        $new_post_link = $post_link;
        
        if( strpos( $post_link, '%year%' ) !== false ) {
            $new_post_link = str_replace( '%year%', date( 'Y', strtotime( $post->post_date ) ), $post_link );
        }
        
        if( strpos( $post_link, '%monthnum%' ) !== false ) {
            $new_post_link = str_replace( '%monthnum%', date( 'm', strtotime( $post->post_date ) ), $new_post_link );
        }
        
        if( strpos( $post_link, '%day%' ) !== false ) {
            $new_post_link = str_replace( '%day%', date( 'd', strtotime( $post->post_date ) ), $new_post_link );
        }
        
        // Check if categoryname or category is in post_link
        if( strpos( $post_link, '%category%' ) === false && strpos( $post_link, '_category_' ) === false && strpos($post_link, '%categoryname%') === false) {
            return $new_post_link;
        }
        
        $terms_key = md5( $post->ID ) . '_category_term';
        if( false === ( $terms = get_transient( $terms_key ) ) ) {
            $terms = wp_get_object_terms( $post->ID, 'category' );
            set_transient( $terms_key, $terms, $cat_ttl );
        }
        
        $category_slug_variations = [ '_category_', '%category%', '%categoryname%' ];
        if( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            
            $slug  = [];
            $order = get_post_meta( $post->ID, 'url_term_order', true );
            
            foreach ( $terms as $term ) {
                $category = $term->slug;
                
                if( false !== $order && is_array( $order ) ) {
                    $key = array_search( $term->term_id, $order, true );
                    
                    if( false !== $key ) {
                        $slug[ $key ] = preg_replace( "/\-([0-9]{1,})$/", '', $category );
                    } else {
                        $slug[] = preg_replace( "/\-([0-9]{1,})$/", '', $category );
                    }
                } else {
                    $slug[] = preg_replace( "/\-([0-9]{1,})$/", '', $category );
                }
            }
            
            ksort( $slug );
            
            return str_replace( $category_slug_variations, implode( '/', $slug ), $new_post_link );
        }
        
        return str_replace( $category_slug_variations, 'article', $new_post_link );
    }
    
    /**
     * Get array of category ids to add to article
     * Creates categories if not exist
     *
     * @param OcArticle $article
     *
     * @since 1.7.0
     * @return array
     */
    public static function add_sanitized_article_categories( OcArticle $article ) {
        $env_settings  = new EnvSettings();
        $cat_prop      = $env_settings->get_category_property();
        $use_hierarchy = $env_settings->get_use_hierarchy_categories();
        $categories    = isset( $article->$cat_prop ) ? $article->$cat_prop : $article->category;
        
        $categories_to_write = [];
        
        if( count( $categories ) > 0 ) {
            
            foreach ( $categories as $index => $category ) {
                if( ! empty( $category ) ) {
                    $sanitized_category = urldecode( sanitize_title_with_dashes( $category ) );
                    
                    // If use hierarchy we get categories with parent
                    if( $use_hierarchy && ( $index > 0 && ! empty( $categories_to_write ) ) ) {
                        
                        // Check against name
                        $category_term = get_terms( 'category', [
                            'name'       => urldecode( $category ),
                            'parent'     => $categories_to_write[ count( $categories_to_write ) - 1 ],
                            'hide_empty' => false,
                            'number'     => 1,
                        ] );
                        
                        // Check against slug. If the name have changed in WordPress
                        if( empty( $category_term ) ) {
                            $category_term = get_terms( 'category', [
                                'slug'       => $sanitized_category,
                                'parent'     => $categories_to_write[ count( $categories_to_write ) - 1 ],
                                'hide_empty' => false,
                                'number'     => 1,
                            ] );
                            
                            // Check against slug combined with parent (WordPress way)
                            if( empty( $category_term ) ) {
                                $category_term = get_terms( 'category', [
                                    'slug'       => urldecode( sanitize_title_with_dashes( $category . ' ' . $categories[ $index - 1 ] ) ),
                                    'parent'     => $categories_to_write[ count( $categories_to_write ) - 1 ],
                                    'hide_empty' => false,
                                    'number'     => 1,
                                ] );
                            }
                        }
                        
                        if( is_array( $category_term ) ) {
                            $category_term = array_shift( $category_term );
                        }
                        
                    } else {
                        $category_term = get_term_by( 'slug', $sanitized_category, 'category' );
                    }
                    
                    if( $category_term === false ) {
                        
                        // If sub-category and use hierarchy insert with parent
                        if( $use_hierarchy && ( $index > 0 && ! empty( $categories_to_write ) ) ) {
                            
                            /** @var WP_Term $inserted_term */
                            $inserted_term = wp_insert_term( $category, 'category', [
                                'parent' => $categories_to_write[ count( $categories_to_write ) - 1 ],
                                'slug'   => strtolower( $category )
                            ] );
                        } else {
                            $inserted_term = wp_insert_term( $category, 'category' );
                        }
                        
                        if( ! is_wp_error( $inserted_term ) ) {
                            $category_id = $inserted_term[ 'term_id' ];
                        }
                    } else {
                        $category_id = $category_term->term_id;
                    }
                    
                    if( $category_id !== null ) {
                        $categories_to_write[] = $category_id;
                    }
                }
            }
            
        } else {
            $categories_to_write = [ 1 ];
        }
        
        return $categories_to_write;
    }
}
