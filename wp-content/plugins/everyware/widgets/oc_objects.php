<?php

class OcObjectsWidget extends OcWidgetBase {

	function oc_widget_name() {
		return 'OC Objects Display';
	}

	/*
	 * Function to render widget content to site
	 */
	function widget( $args, $instance ) {

		extract( $args );

		$this->oc_api->prepare_widget_query( $instance );
		$article_array = $this->oc_api->get_oc_articles();

		print $before_widget;

		$ocobjwid_article_template = null;
		if( isset($instance["article_template"] ) && file_exists( EveryBoard_Settings::get_template_path( $instance["article_template"]) ) ) {
			$ocobjwid_article_template = EveryBoard_Settings::get_template_path($instance["article_template"]);
		}

		$widget_title = isset( $instance['title'] ) ? $instance['title'] : '';

		if( isset( $widget_title ) && $widget_title !== '' ){
			//This will echo widget title, not article title
			print '<div class="widget_title_div">';
			print '<h3 class="widget_title">' . esc_attr( $widget_title ) . '</h3>';
			print '</div>';
		}


		$count = 1;
		foreach ( $article_array as $key => $article ) {

			if($ocobjwid_article_template !== null) {

				$article_img    	= isset( $article_data['article_images'] ) ? $article_data['article_images'][0] : Array();
				$article_title  	= isset( $article->headline[0] ) ? $article->headline[0] : "";
				$artile_leadin  	= isset( $article->leadin[0] ) ? $article->leadin[0] : null;
				$article_author 	= isset( $article->author[0] ) ? $article->author[0] : null;
				$article_text   	= isset( $article->text[0] ) ? $article->text[0] : "";
				$article_counter 	= $count++;

				include $ocobjwid_article_template;
			}
            else if ( file_exists( EveryBoard_Settings::get_template_path( "templates/ocarticle-default.php" ) ) ) {
                include EveryBoard_Settings::get_template_path( "templates/ocarticle-default.php" );
            }
            else {
                $board_renderer = new EveryBoard_Renderer();
                print $board_renderer->render_article_template_fallback( $article );
            }
		}

		print $after_widget;
	}

	function widget_board( $instance ) {

		$this->oc_api->prepare_widget_query( $instance );
		$article_array = $this->oc_api->get_oc_articles();

		print "<br />";

		foreach ( $article_array as $article ) {
			if ( is_object( $article ) && get_class( $article ) === 'OcArticle') {
                $page = OcUtilities::get_article_post_by_uuid($article->uuid[0]);
				$headline 	= isset( $article->headline[0] ) ? $article->headline[0] : '';

				print "<p>";
					print '<strong>' . $headline . '';
						//echo ' <a href="' . get_edit_post_link( $page->ID ) . '" target="_blank" class="settings_link"></a>';
					print '</strong>';
				print "</p>";
			}
		}

	}

	/*
	 * Function to declare admin UI for widget
	 */
	function form( $instance ) {
		$this->title_field( $instance );
		$this->oc_query_field( $instance );
        $this->article_template($instance);
	}

	/*
	 * Function to fetch query for widget.
	 */
    function get_oc_query( $instance ) {

        if(isset($instance)) {
            $this->oc_api->prepare_widget_query( $instance );
            return $this->oc_api->get_active_query();
        }
    }
}

/*
 * Ad widget to flow and hook into widgets_init
 */
//function oc_objects_widget_init() {
//	register_widget( 'OcObjectsWidget' );
//}
//
//add_action( 'widgets_init', 'oc_objects_widget_init' );