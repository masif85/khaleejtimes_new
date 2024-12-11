<?php

class EveryMostReadWidget extends OcWidgetBase {

	function oc_widget_name() {
		return 'Every Most Read';
	}

	/*
	 * Function to display widget
	 */
	function widget( $args, $instance ) {

		extract( $args );


		$limit 		= isset( $instance['limit'] ) ? intval( $instance['limit'] ) : 1;
		$start_date = isset( $instance['start_date'] ) ? $instance['start_date'] : null;
		$stop_date	= isset( $instance['stop_date'] ) ? $instance['stop_date'] : null;
		
		echo $before_widget;
			echo '<div class="every_most_read_widget">';
				
				echo '<div class="every_most_read_widget_header">';
					if( isset( $instance['title'] ) && $instance['title'] !== '' ) {
						echo '<h3>' . $instance['title'] . '</h3>';
					}
				echo '</div>';
				$article_template = null;
		        if( isset($instance["article_template"] ) && file_exists( EveryBoard_Settings::get_template_path( $instance["article_template"]) ) ) {
		            $article_template = EveryBoard_Settings::get_template_path($instance["article_template"]);
		        }

		        // $start_date = strtotime( date('Y-m-d') );
				// $start_date = strtotime( '-7 days', $start_date );
				$article_array = EveryStats::every_stats_get_most_read_articles( $limit, $start_date, $stop_date );

		        $count = 1;
				foreach ( $article_array as $article ) {
					
		            if($article_template !== null) {

		                $article_img    	= isset( $article_data['article_images'] ) ? $article_data['article_images'][0] : Array();
		                $article_title  	= isset( $article->headline[0] ) ? $article->headline[0] : "";
		                $artile_leadin  	= isset( $article->leadin[0] ) ? $article->leadin[0] : null;
		                $article_author 	= isset( $article->author[0] ) ? $article->author[0] : null;
		                $article_text   	= isset( $article->text[0] ) ? $article->text[0] : "";
		                $article_counter 	= $count++;

		                include $article_template;
		            } 
				}

			echo '</div>';
		echo $after_widget;
	}

	function widget_board( $instance ) {
		
		print 'Most read articles widget.';
	}

	/*
	 * Define admin area - aka the properties you need for the given widget
	 */
	function form( $instance ) {
		$this->title_field( $instance );
		$this->article_template( $instance );
		$this->text_field( $instance, 'limit', __('Limit (number of articles to show)', 'every') );
		$this->text_field( $instance, 'start_date', __('Start date (Format example: 2013-04-23)', 'every') );
		$this->text_field( $instance, 'stop_date', __('Stop date (Format example: 2013-04-23)', 'every') );
		// Date somehow.
	}
}

//function every_most_read_widget_init() {
//	register_widget( 'EveryMostReadWidget' );
//}
//add_action( 'widgets_init', 'every_most_read_widget_init' );