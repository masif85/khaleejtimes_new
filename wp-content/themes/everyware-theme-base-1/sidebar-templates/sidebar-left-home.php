<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! is_active_sidebar( 'left-sidebar' ) ) {
	return;
}

// when both sidebars turned on reduce col size to 3 from 4.
$search_form_location = get_theme_mod( 'search_form_location' );
$sidebar_pos = get_theme_mod( 'sidebar_position_homepage' );
$sidebar_width = get_theme_mod( 'sidebar_width' );
?>

<?php 
	if ($sidebar_pos == 'left') {
		if ($sidebar_width == '4_columns') {
				echo '<div class="col-lg-4 col-md-12 order-2 order-lg-1 widget-area" id="left-sidebar" role="complementary">';
		} else {
				echo '<div class="col-lg-3 col-md-12 order-2 order-lg-1 widget-area" id="left-sidebar" role="complementary">';
		}
	} elseif ($sidebar_pos == 'both') {
		if ($sidebar_width == '4_columns') {
				echo '<div class="col-lg-4 col-md-12 order-2 order-lg-1 widget-area" id="left-sidebar" role="complementary">';
		} else {
				echo '<div class="col-lg-3 col-md-12 order-2 order-lg-1 widget-area" id="left-sidebar" role="complementary">';
		}
	} else {
		if ($sidebar_width == '4_columns') {
				echo '<div class="col-lg-4 col-md-12 widget-area" id="left-sidebar" role="complementary">';
		} else {
				echo '<div class="col-lg-3 col-md-12 widget-area" id="left-sidebar" role="complementary">';
		}
	}
	?>

<?php 	
		if ( $search_form_location == 'left_sidebar'){
			echo '<div class="sidebar-search">';
			get_search_form();
			echo '</div>';
		}
?>

<?php dynamic_sidebar( 'left-sidebar' ); ?>


</div><!-- #left-sidebar -->
