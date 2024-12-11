<?php

class WpExtension {

	function __construct( $file ) {

		// When activating plugin create pages needed for the plugin
		//register_activation_hook( $file, array( &$this, 'wpe_set_every_defaults' ) );

		add_action( 'add_meta_boxes', array( &$this, 'add_meta_box_template_preview' ) );
		add_action( 'admin_init', array( &$this, 'wpe_admin_init_hook' ) );
		add_action( 'template_redirect', array( &$this, 'wpe_template_redirect' ) );
	}

	/*
	 * Loads custom article template if existing
	 */
	function wpe_template_redirect() {
		global $post;
		if ( isset( $post->ID ) ) {
			$post_type = get_post_type( $post->ID );

			// Only if the post type is article
			if ( $post_type == 'article' ) {
				$categories = get_the_category( $post->ID );

				// If a category is found check if a template for that category exists and load it if it does
				if ( $categories ) {
					$file = get_stylesheet_directory() . '/single-article-' . $categories[0]->slug . '.php';
					if ( is_file( $file ) ) {

						require_once $file;
						exit;
					}
				}
			}
		}
	}

	/*
	 * Function to setup default pages need by Everyplugin to render Microsites
	 */
	function wpe_set_every_defaults() {

		// Get current User ID
		global $user_ID;

		// Set data for "Home"-page
		$page_home                 = array();
		$page_home['post_type']    = 'page';
		$page_home['post_content'] = '';
		$page_home['post_parent']  = 0;
		$page_home['post_author']  = $user_ID;
		$page_home['post_status']  = 'publish';
		$page_home['post_title']   = 'Home';

		// Set data for "Search"-page
		$page_search                 = array();
		$page_search['post_type']    = 'page';
		$page_search['post_content'] = '';
		$page_search['post_parent']  = 0;
		$page_search['post_author']  = $user_ID;
		$page_search['post_status']  = 'publish';
		$page_search['post_title']   = 'Search';

		// Create the pages
		if ( get_page_by_title( 'Home' ) == null ) {
			wp_insert_post( $page_home );
		}

		if ( get_page_by_title( 'Search' ) == null ) {
			wp_insert_post( $page_search );
		}

		// Set the "Home"-page to be static front page of site
		$home = get_page_by_title( 'Home' );

		update_option( 'page_on_front', $home->ID );
		update_option( 'show_on_front', 'page' );
	}

	/*
	 * Adds a meta box in admin for pages with certain templates.
	 */
	function add_meta_box_template_preview() {
		$template_path = get_page_template();
		$path_parts    = pathinfo( $template_path );
		$preview_path  = get_stylesheet_directory() . '/template-preview/' . $path_parts['filename'] . '.png';

		if ( file_exists( $preview_path ) ) {
			add_meta_box( 'template_preview_meta_box', 'Every Template Preview', array( &$this, 'add_meta_box_template_preview_html' ), 'page', 'normal', 'high' );
		}
	}

	/*
	 * Renders the meta box with template preview
	 */
	function add_meta_box_template_preview_html() {
		$template_path = get_page_template();
		$path_parts    = pathinfo( $template_path );

		$preview_path   = get_stylesheet_directory_uri() . '/template-preview/' . $path_parts['filename'] . '.png';
		$info_file_path = get_stylesheet_directory() . '/template-preview/' . $path_parts['filename'] . '.html';
		$info_html      = "";

		echo '<div style="float: left;"><img src="' . $preview_path . '" /></div>';
		echo '<div style="float: left; padding: 0 25px 0 25px; width: 50%;">';

		if ( file_exists( $info_file_path ) ) {

			$info_html .= file_get_contents( $info_file_path );
			echo $info_html;
		}

		echo '<p>' . _e("This is a preview of your selected every template that shows the structure of the template and it\'s widget areas", "every") . '</p>';
		echo '</div>';

		echo '<div style="clear: both;"></div>';
	}

	/*
	 * Adds a new metabox for the content editor
	 */
	function wpe_admin_init_hook() {
		add_meta_box( 'custom_editor', 'Content', array( &$this, 'wpe_admin_init_hook' ), 'page', 'normal', 'high' );
	}
}