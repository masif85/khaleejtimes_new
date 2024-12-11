<?php

use Infomaker\Everyware\Twig\View;
use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\ArticlePage;
use Infomaker\Everyware\Base\Models\Post;

require get_theme_file_path( '/inc/set_variables/top_bar_variables.php' );
require get_theme_file_path( '/inc/set_variables/sidebar_menu_variables.php' );
require get_theme_file_path('/inc/set_variables/header_bar_variables.php');
require get_theme_file_path('/inc/set_variables/main_nav_variables.php');

$use_google_analytics = get_theme_mod( 'use_google_analytics' );
$google_analytics_id = get_theme_mod( 'google_analytics_id' );
$dfp_code_switch = get_theme_mod( 'dfp_code_switch' );
$dfp_code = get_theme_mod( 'dfp_code' );
$favicon = get_theme_mod_img( 'favicon_image' );
$custom_font_family_url = get_theme_mod( 'custom_font_family_url' );
$topbar_switch = get_theme_mod( 'topbar_switch' );
$main_navigation_switch = get_theme_mod( 'main_navigation_switch' );

if (isset($post->ID)){
	$post_type = get_post_type( $post->ID );
} else {
	$post_type = false;
}

if ($post_type == 'article'){
	$articlePost = Post::createFromPost($post);
}

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>

	<?php
		View::render('@base/theme-templates/head-elements.twig', [
			'custom_font_family_url' => $custom_font_family_url,
			'favicon' => $favicon,
			'stylesheet_uri' => get_stylesheet_directory_uri(),
			'name' => get_bloginfo( 'name' ),
			'description' => get_bloginfo( 'description' ),
			'charset' => get_bloginfo( 'charset' ),
			'pingback_url' => get_bloginfo( 'pingback_url' ),
		]);
		
		/* Sets Article Metadata for Social Media and SEO */
		if ($post_type == 'article'){
			if ($articlePost instanceof Post) {
		                $article = NewsMLArticle::createFromPost($articlePost);
		                $articlePage = new ArticlePage($article);
				$articleArray = $articlePage->getViewData();
				View::render('@base/article-page-metadata', $articleArray);
			}
		}

		if ($dfp_code_switch == 'on' && $dfp_code != ''){
			echo '<script type="text/javascript">';
			echo $dfp_code;
			echo '</script>';
		}

		if ($use_google_analytics == 'yes'){
			View::render('@base/theme-templates/third-party/google-analytics.twig', [
				'post_type' => $post_type,
				'google_analytics_id' => $google_analytics_id
			]);
		}

		wp_head(); 
	?>
</head>

<body <?php body_class(); ?>>

	<?php
		View::render('@base/theme-templates/components/login-modal.twig', [
			'wp_nonce_field' => wp_nonce_field( 'ajax-login-nonce', 'security' )
		]);

		View::render('@base/theme-templates/third-party/facebook-connect.twig', []);

		/******************************************************/
		/*************  Top Bar Region ************************/
		/******************************************************/

		/* Variables set in /inc/set_variables/sidebar_menu_variables.php */ 
		if ($use_hamburger_menu == 'yes'){
			View::render('@base/theme-templates/components/sidebar-menu.twig', [
				'use_hamburger_menu' => $use_hamburger_menu,
				'hamburger_menu_type' => $hamburger_menu_type,
				'sidebar_search_switch' => $sidebar_search_switch,
				'sidebar_search_switch_position' => $sidebar_search_switch_position,
				'sidebar_menu_logo_switch' => $sidebar_menu_logo_switch,
				'sidebar_menu_logo_image' => $sidebar_menu_logo_image,
				'default_sidebar_logo' => $default_sidebar_logo,
				'sidebar_menu' => $sidebar_menu,
				'search_form' => $search_form,
			]);
		}

		if ($topbar_switch == 'on') {
			/* Variables set in /inc/set_variables/top_bar_variables.php */
			View::render('@base/theme-templates/components/top-bar.twig', [
				'use_hamburger_menu' => $use_hamburger_menu,
				'hamburger_menu_width' => $hamburger_menu_width,
				'full_length_topbar_menu' => $full_length_topbar_menu,
				'topbar_full_width' => $topbar_full_width,
				'topbar_left_toggle' => $topbar_left_toggle,
				'topbar_left_choice' => $topbar_left_choice,
				'topbar_center_toggle' => $topbar_center_toggle,
				'topbar_center_choice' => $topbar_center_choice,
				'topbar_right_toggle' => $topbar_right_toggle,
				'topbar_right_choice' => $topbar_right_choice,
				'logged_in' => $logged_in,
				'current_user_name' => $current_user_name,
				'logout_url' => $logout_url,
				'search_form' => $search_form,
				'top_menu_left' => $top_menu_left,
				'left_widget_active' => $left_widget_active,
				'left_widget_area' => $left_widget_area,
				'top_menu_center' => $top_menu_center,
				'center_widget_active' => $center_widget_active,
				'center_widget_area' => $center_widget_area,
				'top_menu_right' => $top_menu_right,
				'right_widget_active' => $right_widget_active,
				'right_widget_area' => $right_widget_area,
				'hamburger_menu_text' => $hamburger_menu_text,
				'logo_top_bar_mobile' => $logo_top_bar_mobile,
				'logo_top_bar_mobile_on_scroll' => $logo_top_bar_mobile_on_scroll,
				'top_bar_logo_image' => $top_bar_logo_image,
				'the_custom_logo' => $the_custom_logo,
				'drop_nav_menu' => $drop_nav_menu,
				'hamburger_menu_type' => $hamburger_menu_type,
				'search_form_location' => $search_form_location,
				'search_form_location_mobile_toggle' => $search_form_location_mobile_toggle,
				'get_search_form' => $get_search_form,
			]);
		}

		/*****************************************************/
		/*************  END Top Bar Region *******************/
		/*****************************************************/

		/*****************************************************/
		/**************  Header Region ***********************/
		/*****************************************************/

        /* Variables set in /inc/set_variables/header_bar_variables.php */
        View::render('@base/theme-templates/components/header-bar.twig', [
            'header_widgets' => $header_widgets,
            'logo_pos' => $logo_pos,
            'header_left_switch' => $header_left_switch,
            'header_left_code' => $header_left_code,
            'header_left_size' => $header_left_size,
            'header_right_switch' => $header_right_switch,
            'header_right_code' => $header_right_code,
            'header_right_size' => $header_right_size,
            'header_region_sidebar' => $header_region_sidebar,
            'has_custom_logo' => $has_custom_logo,
            'is_front_page' => $is_front_page,
            'is_home' => $is_home,
            'home_url' => $home_url,
            'get_blog_info' => $get_blog_info,
            'blog_info' => $blog_info,
            'the_custom_logo' => $the_custom_logo,
            'under_nav_ad_switch' => $underNavAd_switch,
			'under_nav_ad_code' => $underNavAd_code,
			'logo_top_bar_mobile' => $logo_top_bar_mobile,
			'default_logo' => $default_logo
        ]);

		if ($main_navigation_switch == 'on') {
			/* Variables set in /inc/set_variables/main_nav_variables.php and top_bar_variables.php */
			View::render('@base/theme-templates/components/main-nav-bar.twig', [
				'use_main_navigation_menu' => $use_main_navigation_menu,
				'main_nav_menu' => $main_nav_menu,
				'search_form_location' => $search_form_location,
				'get_search_form' => $get_search_form,
				'search_form_location_mobile_toggle' => $search_form_location_mobile_toggle
			]);
		}
	?>
	
	<!--Ad Code for Leaderboard Ad above Footer-->
	<?php if ( $underNavAd_switch == 'on' ) : ?>
		<div id="<?php echo $underNavAd_code; ?>" class="underNavAd advertisement col-md-12">
			<script type="text/javascript">
				if (googletag !== undefined) {
					googletag.cmd.push(function() { googletag.display("<?php echo $underNavAd_code; ?>"); })
				}
			</script>
		</div>
	<?php endif; ?>

	<!-- ***************************************************** -->
	<!-- **************  END Header Region ******************* -->
	<!-- ***************************************************** -->