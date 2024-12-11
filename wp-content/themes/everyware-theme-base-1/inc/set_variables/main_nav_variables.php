<?php

$search_form_location = get_theme_mod( 'search_form_location' );
$main_nav_menu = wp_nav_menu(
    array(
        'theme_location'  => 'main-menu',
        'container_class' => 'collapse navbar-collapse',
        'container_id'    => 'navbarNavDropdown',
        'menu_class'      => 'navbar-nav',
        'fallback_cb'     => '',
        'menu_id'         => 'main-menu',
        'depth'           => 4,
        'walker'          => new Understrap_WP_Bootstrap_Navwalker_everyware_theme_base_1(),
        'echo'            => false,
    )
);
$get_search_form = get_search_form(false);

$underNavAd_switch = get_theme_mod( 'underNavAd_switch' );
$underNavAd_code = get_theme_mod( 'underNavAd_code' );
$search_form_location_mobile_toggle = get_theme_mod( 'search_form_location_mobile_toggle' );