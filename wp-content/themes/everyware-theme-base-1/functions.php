<?php

/**
 * Calls CSS and JS Files.
 */

use Infomaker\Everyware\Base\ProjectStartup;
use USKit\Base\Startup;

ProjectStartup::registerThemeStartup(new Startup);

function add_jquery() {
    wp_enqueue_script('jquery', ("https://code.jquery.com/jquery-3.3.1.min.js"), false, '3.3.1', true);
 }    
add_action('wp_enqueue_scripts', 'add_jquery');

function enqueue_styles_scripts() {
        wp_enqueue_style( 'understrap', get_stylesheet_directory_uri().'/css/understrap.min.css' ); 
        wp_enqueue_script( 'understrap', get_stylesheet_directory_uri().'/js/understrap.min.js', '', '', true ); 
        wp_enqueue_style( 'slick', get_stylesheet_directory_uri().'/slick/slick.css' );
        wp_enqueue_style( 'slick-theme', get_stylesheet_directory_uri().'/slick/slick-theme.css', '', '' );
        wp_enqueue_script( 'slick', get_stylesheet_directory_uri().'/slick/slick.js','', '', true );
        wp_enqueue_script( 'slick-theme', get_stylesheet_directory_uri().'/slick/slick-theme.js', '', '', true );
        wp_enqueue_script( 'menus', get_stylesheet_directory_uri().'/js/menus.js', '', '', true);
        if (get_theme_mod('mainnav_sticky_toggle') == 'on') {
            wp_enqueue_script( 'sticky-nav', get_stylesheet_directory_uri().'/js/sticky-nav.js', '', '', true);
        }
        wp_enqueue_script( 'breaking', get_stylesheet_directory_uri().'/js/breaking.js', '', '', true );
        wp_enqueue_script( 'content', get_stylesheet_directory_uri().'/js/content.js', '', '', true );
        wp_enqueue_style( 'style', get_stylesheet_uri() );
        wp_enqueue_style( 'base-theme', get_stylesheet_directory_uri().'/css/base-theme.min.css' );
        wp_add_inline_style( 'style', everyware_theme_base_1_child_customizer_css() );
}
add_action( 'wp_enqueue_scripts', 'enqueue_styles_scripts' );

add_theme_support( 'title-tag' );
add_theme_support( 'custom-logo' );

/**
 * Custom Theme Settings.
 */
require get_theme_file_path( 'inc/custom_customizer_settings.php' );

/**
 * Custom Ad Spots Widget.
 */
require get_theme_file_path( 'inc/adspots_widget.php' );

/**
 * Load custom WordPress nav walker.
 */
require get_theme_file_path( '/inc/class-wp-bootstrap-navwalker-everyware-theme-base-1.php' );

/**
 * Load custom WordPress nav walker.
 */
require get_theme_file_path( '/inc/class-wp-bootstrap-navwalker-dropdown-everyware-theme-base-1.php' );


/**
 * Load custom WordPress nav walker.
 */
require get_theme_file_path( '/inc/class-wp-bootstrap-navwalker.php' );


/**
 * Adding Top Navigation and Footer Menu 
 */
function register_menus() {
    register_nav_menu('top_navigation_left',__( 'Top Bar Menu - Left' ));
    register_nav_menu('top_navigation_right',__( 'Top Bar Menu - Right' ));
    register_nav_menu('top_navigation_center',__( 'Top Bar Menu - Center' ));
    register_nav_menu('footer_menu_1',__( 'Footer Menu - 1' ));
    register_nav_menu('footer_menu_2',__( 'Footer Menu - 2' ));
    register_nav_menu('footer_menu_3',__( 'Footer Menu - 3' ));
    register_nav_menu('footer_menu_4',__( 'Footer Menu - 4' ));
    register_nav_menu('hamburger_menu',__( 'Hamburger Menu' ));
  }
  add_action( 'init', 'register_menus' );

/**
 * Add User Specific Login and Logout links to Top Navigation
 */
function everyware_theme_base_1_login_register_menu_top_left( $items, $args ) {
    if( $args->theme_location == 'top_navigation_left' ){

        $current_user = wp_get_current_user();

        if ( is_user_logged_in() ) {
            $user_items = '<li class="top-navigation-welcome"><a href="/wp-admin/index.php"><i class="fa fa-user-circle-o welcomeicon"></i>Welcome, ' . esc_html( $current_user->user_login ) . '</a></li>';
            $user_items .= '<li class="top-navigation-logout"><a href="' . wp_logout_url( home_url() ) . '">' . __( 'Log Out' ) . '</a></li>';
        } else {
            $user_items = '<li class="top-navigation-welcome"><i class="fa fa-user-circle-o welcomeicon"></i>Welcome, User</li>';
            $user_items .= '<li class="top-navigation-login" ><a href="" id="show_login">' . __( 'Login' ) . '</a></li>';
            $user_items .= '<li class="top-navigation-register"><a href="/register/">' . __( 'Register' ) . '</a></li>';
        }

        $user_menu = $user_items . $items;
        return $user_menu;    
    }
    return $items;
}

function everyware_theme_base_1_login_register_menu_top_right( $items, $args ) {
    if( $args->theme_location == 'top_navigation_right' ){

        $current_user = wp_get_current_user();

        if ( is_user_logged_in() ) {
            $user_items = '<li class="top-navigation-welcome"><a href="/wp-admin/index.php"><i class="fa fa-user-circle-o welcomeicon"></i>Welcome, ' . esc_html( $current_user->user_login ) . '</a></li>';
            $user_items .= '<li class="top-navigation-logout"><a href="' . wp_logout_url( home_url() ) . '">' . __( 'Log Out' ) . '</a></li>';
        } else {
            $user_items = '<li class="top-navigation-welcome"><i class="fa fa-user-circle-o welcomeicon"></i>Welcome, User</li>';
            $user_items .= '<li class="top-navigation-login" ><a href="" id="show_login">' . __( 'Login' ) . '</a></li>';
            $user_items .= '<li class="top-navigation-register"><a href="/register/">' . __( 'Register' ) . '</a></li>';
        }

        $user_menu = $user_items . $items;
        return $user_menu;    
    }
    return $items;
}

$topbar_login_data_position= get_theme_mod( 'topbar_login_data_position');
if ($topbar_login_data_position == 'left' ) {
    add_filter( 'wp_nav_menu_items', 'everyware_theme_base_1_login_register_menu_top_left', 199, 2 );
} else {
    add_filter( 'wp_nav_menu_items', 'everyware_theme_base_1_login_register_menu_top_right', 199, 2 );
}

function everyware_theme_base_1_ajax_login_init(){

    wp_enqueue_script( 'login-modal', get_stylesheet_directory_uri().'/js/login-modal.js', array( 'jquery' ));

    wp_localize_script( 'login-modal', 'ajax_login_object', array( 
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'redirecturl' => home_url(),
        'loadingmessage' => __('')
    ));

    add_action( 'wp_ajax_nopriv_ajaxlogin', 'everyware_theme_base_1_ajax_login' );
}

if (!is_user_logged_in()) {
    add_action('init', 'everyware_theme_base_1_ajax_login_init');
}


function everyware_theme_base_1_ajax_login(){

    check_ajax_referer( 'ajax-login-nonce', 'security' );

    $info = array();
    $info['user_login'] = $_POST['username'];
    $info['user_password'] = $_POST['password'];
    $info['remember'] = true;

    $user_signon = wp_signon( $info );
    if ( is_wp_error($user_signon) ){
        echo json_encode(array('loggedin'=>false, 'message'=>__('Wrong username or password.')));
    } else {
        echo json_encode(array('loggedin'=>true, 'message'=>__('')));
    }

    die();
}


/**
 * Register Top Bar and Footer Menu Widget Section
 */
function everyware_theme_base_1_register_widgets() {
    register_sidebar( array(
        'name'          => __( 'Top Bar - Left', 'everyware-theme-base-1' ),
        'id'            => 'top-bar-left',
        'description'   => __( 'Top Bar - Left widget area', 'everyware-theme-base-1' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Top Bar - Center', 'everyware-theme-base-1' ),
        'id'            => 'top-bar-center',
        'description'   => __( 'Top Bar - Center widget area', 'everyware-theme-base-1' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Top Bar - Right', 'everyware-theme-base-1' ),
        'id'            => 'top-bar-right',
        'description'   => __( 'Top Bar - Right widget area', 'everyware-theme-base-1' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Header Region', 'everyware-theme-base-1' ),
        'id'            => 'header-region',
        'description'   => __( 'Header Region area', 'everyware-theme-base-1' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Above Footer Widget Area', 'everyware-theme-base-1' ),
        'id'            => 'above-footer-area',
        'description'   => __( 'Above Footer Widget Area', 'everyware-theme-base-1' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );
    register_sidebar( array(
        'name'          => __( 'Right Sidebar', 'everyware-theme-base-1' ),
        'id'            => 'right-sidebar',
        'description'   => __( 'Right sidebar widget area', 'everyware-theme-base-1' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Left Sidebar', 'everyware-theme-base-1' ),
        'id'            => 'left-sidebar',
        'description'   => __( 'Left sidebar widget area', 'everyware-theme-base-1' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'everyware_theme_base_1_register_widgets' );

$header_widgets_columns = get_theme_mod( 'header_widgets_columns');
/**
 * Register Header Widget Section based with columns based on 'header_widgets_columns' setting
 */
function everyware_theme_base_1_register_header_widget() {
    register_sidebar( array(
        'name'          => __( 'Header Region', 'everyware-theme-base-1' ),
        'id'            => 'header-region',
        'description'   => __( 'Header Region area', 'everyware-theme-base-1' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );
}

function everyware_theme_base_1_register_header_widget_columns() {
    register_sidebar( array(
        'name'          => __( 'Header Region', 'everyware-theme-base-1' ),
        'id'            => 'header-region',
        'description'   => __( 'Header Region area', 'everyware-theme-base-1' ),
        'before_widget' => '<aside id="%1$s" class="widget col-md-4 %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );
}
if ($header_widgets_columns == 'columns') {
add_action( 'widgets_init', 'everyware_theme_base_1_register_header_widget_columns' );
} else {
add_action( 'widgets_init', 'everyware_theme_base_1_register_header_widget' );
}

function remove_widget_title( $widget_title ) {
    if ( substr ( $widget_title, 0, 1 ) == '!' )
        return;
    else
        return ( $widget_title );
}
add_filter( 'widget_title', 'remove_widget_title' );

function everyware_theme_base_1_deregister_widgets(){

	unregister_sidebar( 'hero' );
    unregister_sidebar( 'herocanvas' );
    unregister_sidebar( 'statichero' );
    unregister_sidebar( 'footerfull' );
    unregister_sidebar( 'ew-before-footer' );
    unregister_sidebar( 'ew-after-footer' );
    
}
add_action( 'widgets_init', 'everyware_theme_base_1_deregister_widgets', 11 );

/* Function to deal with images being uploaded returning HTTP instead of HTTPS */
function get_theme_mod_img($mod_name){
    return str_replace(array('http:', 'https:'), '', get_theme_mod($mod_name));
}

function everyware_theme_base_1_hide_wordpress_setting() {
    global $wp_customize;
    $wp_customize->remove_setting( 'site_icon' );  
} 
add_action( 'customize_register', 'everyware_theme_base_1_hide_wordpress_setting');

