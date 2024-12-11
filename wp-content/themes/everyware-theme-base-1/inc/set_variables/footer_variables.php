<?php 

/**
 * This file is where we are setting all of our variables used within the Footer.
 */

$use_footer_menu_one = get_theme_mod( 'use_footer_menu_one' );
$use_footer_menu_two = get_theme_mod( 'use_footer_menu_two' );
$use_footer_menu_three = get_theme_mod( 'use_footer_menu_three' );
$use_footer_menu_four = get_theme_mod( 'use_footer_menu_four' );
$footer_menu_one_heading = get_theme_mod( 'footer_menu_one_heading' );
$footer_menu_two_heading = get_theme_mod( 'footer_menu_two_heading' );
$footer_menu_three_heading = get_theme_mod( 'footer_menu_three_heading' );
$footer_menu_four_heading = get_theme_mod( 'footer_menu_four_heading' );
$use_footer_menu_contactinfo = get_theme_mod( 'use_footer_menu_contactinfo' );
$footer_menu_contactinfo_street = get_theme_mod( 'footer_menu_contactinfo_street' );
$footer_menu_contactinfo_city_state_zip = get_theme_mod( 'footer_menu_contactinfo_city_state_zip' );
$footer_menu_contactinfo_phone = get_theme_mod( 'footer_menu_contactinfo_phone' );
$use_footer_social_media = get_theme_mod( 'use_footer_social_media' );
$footer_social_media_facebook = get_theme_mod( 'footer_social_media_facebook' );
$footer_social_media_instagram = get_theme_mod( 'footer_social_media_instagram' );
$footer_social_media_youtube = get_theme_mod( 'footer_social_media_youtube' );
$footer_social_media_twitter = get_theme_mod( 'footer_social_media_twitter' );
$use_footer_copyright = get_theme_mod( 'use_footer_copyright' );
$footer_copyright_position = get_theme_mod( 'footer_copyright_position' );
$footer_copyright_text = get_theme_mod( 'footer_copyright_text' );
$footer_logo_switch = get_theme_mod( 'footer_logo_switch' );
$footer_main_position = get_theme_mod( 'footer_main_position' );
$footer_logo_image = get_theme_mod( 'footer_logo_image' );
$default_footer_logo = get_stylesheet_directory_uri() . '/images/default_footer-logo.png';
$footerAd_switch = get_theme_mod( 'footerAd_switch' );
$footerAd_code = get_theme_mod( 'footerAd_code' );
$is_front_page = is_front_page();
$is_home = is_home();
$site_name = get_bloginfo( 'name' );

$footer_menu_1 = wp_nav_menu(
    array(
        'theme_location'  => 'footer_menu_1',
        'container_class' => '',
        'container_id'    => '',
        'menu_class'      => '',
        'fallback_cb'     => '',
        'menu_id'         => 'Footer Menu - 1',
        'depth'           => 2,
        'walker'          => new Understrap_WP_Bootstrap_Navwalker(),
		'echo'			  => false
    )
);

$footer_menu_2 = wp_nav_menu(
    array(
        'theme_location'  => 'footer_menu_2',
        'container_class' => '',
        'container_id'    => '',
        'menu_class'      => '',
        'fallback_cb'     => '',
        'menu_id'         => 'Footer Menu - 2',
        'depth'           => 2,
        'walker'          => new Understrap_WP_Bootstrap_Navwalker(),
		'echo'			  => false
    )
);

$footer_menu_3 = wp_nav_menu(
    array(
        'theme_location'  => 'footer_menu_3',
        'container_class' => '',
        'container_id'    => '',
        'menu_class'      => '',
        'fallback_cb'     => '',
        'menu_id'         => 'Footer Menu - 3',
        'depth'           => 2,
        'walker'          => new Understrap_WP_Bootstrap_Navwalker(),
		'echo'			  => false
    )
);

$footer_menu_4 = wp_nav_menu(
    array(
        'theme_location'  => 'footer_menu_4',
        'container_class' => '',
        'container_id'    => '',
        'menu_class'      => '',
        'fallback_cb'     => '',
        'menu_id'         => 'Footer Menu - 4',
        'depth'           => 2,
        'walker'          => new Understrap_WP_Bootstrap_Navwalker(),
		'echo'			  => false
    )
);

?>
