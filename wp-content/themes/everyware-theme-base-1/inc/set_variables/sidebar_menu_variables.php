<?php 
	
/**
 * This file is where we are setting all of our variables used within the Sidebar Menu.
 */

$use_hamburger_menu = get_theme_mod( 'use_hamburger_menu' );
$hamburger_menu_type = get_theme_mod('hamburger_menu_type');
$sidebar_menu_logo_switch = get_theme_mod( 'sidebar_menu_logo_switch' );
$sidebar_menu_logo_image = get_theme_mod( 'sidebar_menu_logo_image' );
$default_sidebar_logo = get_stylesheet_directory_uri() . '/images/default_logo.png';
$sidebar_search_switch = get_theme_mod( 'sidebar_search_switch' );
$sidebar_search_switch_position = get_theme_mod( 'sidebar_search_switch_position' );
$search_form = get_search_form( false );

$sidebar_menu = wp_nav_menu(
	array(
		'theme_location'  => 'hamburger_menu',
		'container_class' => 'sidebarMenu',
		'container_id'    => 'sidebarMenu',
		'menu_class'      => 'list-group list-group-flush',
		'after'			  => '<hr class="sidebarMenuLine">',
		'fallback_cb'     => '',
		'menu_id'         => '',
		'depth'           => 4,
		'walker'          => new Understrap_WP_Bootstrap_Navwalker_everyware_theme_base_1(),
		'echo'			  => false
		)
); 

?>