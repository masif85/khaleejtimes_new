<?php 

/**
 * This file is where we are setting all of our variables used within the Top Bar.
 */

$topbar_widgets = get_theme_mod( 'topbar_widgets' ); 
$use_hamburger_menu = get_theme_mod( 'use_hamburger_menu' );
$use_main_navigation_menu = get_theme_mod( 'use_main_navigation_menu' );
$full_length_topbar_menu = get_theme_mod( 'full_length_topbar_menu' );
$topbar_full_width = get_theme_mod( 'topbar_full_width' );
$topbar_left_toggle = get_theme_mod( 'topbar_left_toggle' );
$topbar_left_choice = get_theme_mod( 'topbar_left_choice' );
$topbar_center_toggle = get_theme_mod( 'topbar_center_toggle' );
$topbar_center_choice = get_theme_mod( 'topbar_center_choice' );
$topbar_right_toggle = get_theme_mod( 'topbar_right_toggle' );
$topbar_right_choice = get_theme_mod( 'topbar_right_choice' );
$hamburger_menu_width = get_theme_mod( 'hamburger_menu_width' );
$hamburger_menu_text = get_theme_mod( 'hamburger_menu_text' );
$sidebar_menu_logo_switch = get_theme_mod( 'sidebar_menu_logo_switch' );
$sidebar_menu_logo_image = get_theme_mod( 'sidebar_menu_logo_image' );
$sidebar_search_switch = get_theme_mod( 'sidebar_search_switch' );
$sidebar_search_switch_position = get_theme_mod( 'sidebar_search_switch_position' );
$logo_top_bar_mobile = get_theme_mod( 'logo_top_bar_mobile' );
$logo_top_bar_mobile_on_scroll = get_theme_mod( 'logo_top_bar_mobile_on_scroll' );
$top_bar_logo_image = get_theme_mod( 'top_bar_logo_image' );
$hamburger_menu_type = get_theme_mod('hamburger_menu_type');
$the_custom_logo = get_custom_logo();
global $current_user; 
$logged_in = is_user_logged_in();
$current_user_name = $current_user->user_login;
$logout_url =  wp_logout_url( home_url() );
$search_form = get_search_form( false );

if ($topbar_left_choice == 'widget_area'){
    $left_widget_active = is_active_sidebar( 'top-bar-left');
	ob_start();
	dynamic_sidebar('top-bar-left');
	$left_widget_area = ob_get_contents();
	ob_end_clean();
} else {
    $left_widget_active = false;
    $left_widget_area = false;		
}

if ($topbar_center_choice == 'widget_area'){
    $center_widget_active = is_active_sidebar( 'top-bar-center');
	ob_start();
	dynamic_sidebar('top-bar-center');
	$center_widget_area = ob_get_contents();
	ob_end_clean();
} else {
    $center_widget_active = false;
    $center_widget_area = false;		
}

if ($topbar_right_choice == 'widget_area'){
    $right_widget_active = is_active_sidebar( 'top-bar-right');
	ob_start();
	dynamic_sidebar('top-bar-right');
	$right_widget_area = ob_get_contents();
	ob_end_clean();
} else {
    $right_widget_active = false;
    $right_widget_area = false;
}

$top_menu_left = wp_nav_menu(
    array(
        'theme_location'  => 'top_navigation_left',
        'container_class' => 'collapse navbar-collapse',
        'container_id'    => 'topMenu_left',
        'menu_class'      => 'navbar-nav navbar',
        'fallback_cb'     => '',
        'menu_id'         => 'Top Bar - Left',
        'depth'           => 2,
        'walker'          => new Understrap_WP_Bootstrap_Navwalker(),
        'echo'			  => false
    )
);

$top_menu_center = wp_nav_menu(
array(
        'theme_location'  => 'top_navigation_center',
        'container_class' => '',
        'container_id'    => 'topMenu_center',
        'menu_class'      => 'navbar-nav navbar',
        'fallback_cb'     => '',
        'menu_id'         => 'Top Bar - Center',
        'depth'           => 2,
        'walker'          => new Understrap_WP_Bootstrap_Navwalker(),
        'echo'			  => false
        )
);	

$top_menu_right = wp_nav_menu(
array(
        'theme_location'  => 'top_navigation_right',
        'container_class' => 'collapse navbar-collapse',
        'container_id'    => 'topMenu_right',
        'menu_class'      => 'navbar-nav navbar',
        'fallback_cb'     => '',
        'menu_id'         => 'Top Bar - right',
        'depth'           => 2,
        'walker'          => new Understrap_WP_Bootstrap_Navwalker(),
        'echo'			  => false
        )
);

$drop_nav_menu = wp_nav_menu(
    array(
        'theme_location'  => 'hamburger_menu',
        'container_class' => '',
        'container_id'    => 'navbarNavDropdown',
        'menu_class'      => 'dropdown-menu',
        'fallback_cb'     => '',
        'menu_id'         => 'top-main-menu',
        'depth'           => 2,
        'walker'          => new Understrap_WP_Bootstrap_Navwalker_Dropdown_everyware_theme_base_1(),
        'echo'            => false,
    )
);

