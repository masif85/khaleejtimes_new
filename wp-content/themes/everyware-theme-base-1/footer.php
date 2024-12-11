<?php
use Infomaker\Everyware\Twig\View;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/* Set variables used within footer */
require get_theme_file_path( '/inc/set_variables/footer_variables.php' );
$above_footer_widget_area_switch = get_theme_mod( 'above_footer_widget_area_switch' );
?>

<?php get_template_part( 'sidebar-templates/sidebar', 'footerfull' ); ?>

	<!--Ad Code for leaderboard ad above footer-->
	<?php if ( $footerAd_switch == 'on' ) : ?>
		<div id="<?php echo $footerAd_code; ?>" class="footerAd advertisement col-md-12">
			<script type="text/javascript">
				if (googletag !== undefined) {
					googletag.cmd.push(function() { googletag.display("<?php echo $footerAd_code; ?>"); })
				}
			</script>
		</div>
	<?php endif; ?>

	<!--Ad Code for leaderboard ad above footer-->
	<?php if ( $above_footer_widget_area_switch == 'on' ) : ?>
		<div class="above_footer_widget_area">
			<div class="container">
				<div class="above_footer_widget_items"><?php dynamic_sidebar('above-footer-area'); ?></div>
			</div>
		</div>
	<?php endif; ?>

	<?php 
		/* Variables set in /inc/set_variables/footer_variables.php */
		View::render('@base/theme-templates/components/footer.twig', [
			'use_footer_menu_one' => $use_footer_menu_one,
			'use_footer_menu_two' => $use_footer_menu_two,
			'use_footer_menu_three' => $use_footer_menu_three,
			'use_footer_menu_four' =>  $use_footer_menu_four,
			'footer_menu_one_heading' => $footer_menu_one_heading,
			'footer_menu_two_heading' => $footer_menu_two_heading,
			'footer_menu_three_heading' => $footer_menu_three_heading,
			'footer_menu_four_heading' => $footer_menu_four_heading,
			'use_footer_menu_contactinfo' => $use_footer_menu_contactinfo,
			'footer_menu_contactinfo_street' => $footer_menu_contactinfo_street,
			'footer_menu_contactinfo_city_state_zip' => $footer_menu_contactinfo_city_state_zip,
			'footer_menu_contactinfo_phone' => $footer_menu_contactinfo_phone,
			'use_footer_social_media' => $use_footer_social_media,
			'footer_social_media_facebook' => $footer_social_media_facebook,
			'footer_social_media_instagram' => $footer_social_media_instagram,
			'footer_social_media_youtube' => $footer_social_media_youtube,
			'footer_social_media_twitter' => $footer_social_media_twitter,
			'use_footer_copyright' => $use_footer_copyright,
			'footer_copyright_position' => $footer_copyright_position,
			'footer_copyright_text' => $footer_copyright_text,
			'footer_logo_switch' => $footer_logo_switch,
			'footer_logo_image' => $footer_logo_image,
			'footer_main_position' => $footer_main_position,
			'default_footer_logo' => $default_footer_logo,
			'footer_menu_1' => $footer_menu_1,
			'footer_menu_2' => $footer_menu_2,
			'footer_menu_3' => $footer_menu_3,
			'footer_menu_4' => $footer_menu_4,
			'is_front_page' => $is_front_page,
			'is_home' => $is_home,
			'site_name' => $site_name,
		]);
	?>

	<?php wp_footer(); ?>

	</body>
</html>

