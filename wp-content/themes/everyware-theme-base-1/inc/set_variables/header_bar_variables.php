<?php

$header_widgets = get_theme_mod( 'header_widgets' );
$logo_pos = get_theme_mod( 'logo_position' );
$default_logo = get_stylesheet_directory_uri() . '/images/default_logo.png';
$header_left_switch = get_theme_mod( 'header_left_switch' );
$header_left_code = get_theme_mod( 'header_left_code' );
$header_left_size = get_theme_mod( 'header_left_size' );
$header_right_switch = get_theme_mod( 'header_right_switch' );
$header_right_code = get_theme_mod( 'header_right_code' );
$header_right_size = get_theme_mod( 'header_right_size' );
$logo_top_bar_mobile = get_theme_mod( 'logo_top_bar_mobile' );

if ($header_widgets == 'on'){
	ob_start();
	dynamic_sidebar('header-region');
	$header_region_sidebar = ob_get_contents();
	ob_end_clean();
} else {
    $header_region_sidebar = false;	
}

$has_custom_logo = has_custom_logo();
$is_front_page = is_front_page();
$is_home = is_home();
$home_url = esc_url( home_url( '/' ));
$get_blog_info = esc_attr( get_bloginfo( 'name', 'display' ));
$blog_info = get_bloginfo('name');
$the_custom_logo = get_custom_logo();

