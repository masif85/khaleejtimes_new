<?php

class OcWidgetName extends OcWidgetBase {

	/*
	 * Function to be called by parent constructor, will give the widget its name
	 */
	function oc_widget_name() {
		return 'A Widget name goes here...'; //Set widget name here
	}

	/*
	 * Function to display widget
	 * Use OcUtilities to render content
	 */
	function widget( $args, $instance ) {

		//Make all arguments available as variables
		extract( $args );

		$this->oc_api->prepare_widget_query( $instance );

		$article_array = $this->oc_api->get_oc_articles( );

		echo $before_widget;
		echo $before_title . $instance['title'] . $after_title;

		//Use OcUtilities to render content like this
		OcUtilities::render_article_headline( $article );
		OcUtilities::render_article_text( $article, $instance, false );

		echo $after_widget;
	}

	/*
	 * Define admin area - aka the properties you need for the given widget
	 */
	function form( $instance ) {
		$this->title_field( $instance ); //will produce variable "title"
		$this->oc_query_field( $instance ); //will produce variable "oc_query"
		$this->text_length_field( $instance ); //will produce variable "text_length"
	}
}

//Give this function a unique name
function oc_widget_init() {
	//register_widget('OcWidgetName'); //uncomment this line and put Widget class-name as parameter
}

//Add function above as second param
add_action( 'widgets_init', 'oc_widget_init' );