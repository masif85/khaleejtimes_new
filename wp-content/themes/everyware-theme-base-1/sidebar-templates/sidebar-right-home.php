<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! is_active_sidebar( 'right-sidebar' ) ) {
	return;
}

// when both sidebars turned on reduce col size to 3 from 4.
$search_form_location = get_theme_mod( 'search_form_location' );
$sidebar_pos = get_theme_mod( 'sidebar_position_homepage' );
$sidebar_width = get_theme_mod( 'sidebar_width' );
$skybox_switch = get_theme_mod( 'skybox_switch' );
$skybox_code = get_theme_mod( 'skybox_code' );
$skyscraper_switch = get_theme_mod( 'skyscraper_switch' );
$skyscraper_code = get_theme_mod( 'skyscraper_code' );
?>

<?php
	if ($sidebar_pos == 'both') {
		if ($sidebar_width == '4_columns') {
				echo '<div class="col-lg-4 col-md-12 order-3 order-lg-3 widget-area" id="right-sidebar" role="complementary">';
		} else {
				echo '<div class="col-lg-3 col-md-12 order-3 order-lg-3 widget-area" id="right-sidebar" role="complementary">';
		}
	} else {
		if ($sidebar_width == '4_columns') {
			echo '<div class="col-lg-4 col-md-12 widget-area" id="right-sidebar" role="complementary">';
		} else {
			echo '<div class="col-lg-3 col-md-12 widget-area" id="right-sidebar" role="complementary">';
		}		
	}
	?>

<?php 	
		if ( $search_form_location == 'right_sidebar'){
			echo '<div class="sidebar-search">';
			get_search_form();
			echo '</div>';
		}
?>

<?php dynamic_sidebar( 'right-sidebar' ); ?>

</div><!-- #right-sidebar -->
