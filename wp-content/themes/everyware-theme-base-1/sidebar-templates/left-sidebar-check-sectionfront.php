<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<?php
$sidebar_pos = get_theme_mod( 'sidebar_position_sectionfront' );
$sidebar_width = get_theme_mod( 'sidebar_width' );
?>

<?php if ( 'left' === $sidebar_pos || 'both' === $sidebar_pos ) : ?>
	<?php get_template_part( 'sidebar-templates/sidebar-left-section' ); ?>
<?php endif; ?>

<?php
	$html = '';
	if ( 'right' === $sidebar_pos || 'left' === $sidebar_pos ) {
		$html = '<div class="';
		if ('left' === $sidebar_pos) {
			if ($sidebar_width == '4_columns'){
				$html .= 'col-lg-8 col-md-12 order-1 order-lg-2 content-area" id="everyware-theme-base-1">';
			} else {
				$html .= 'col-lg-9 col-md-12 order-1 order-lg-2 col-sm-12 content-area" id="everyware-theme-base-1">';
			}
		} elseif ('right' === $sidebar_pos ) {
			if ($sidebar_width == '4_columns'){
				$html .= 'col-lg-8 col-md-12 content-area" id="everyware-theme-base-1">';
			} else {
				$html .= 'col-lg-9 col-md-12 col-sm-12 content-area" id="everyware-theme-base-1">';
			}
		}
		else {
			$html .= 'col-md-12 content-area" id="everyware-theme-base-1">';
		}
		echo $html; // WPCS: XSS OK.
	} elseif ( 'both' === $sidebar_pos ) {
		$html = '<div class="';
			if ($sidebar_width == '4_columns'){
				$html .= 'col-lg-4 col-md-12 order-1 order-lg-2 col-sm-12 content-area" id="everyware-theme-base-1">';
			} else {
				$html .= 'col-lg-6 col-md-12 order-1 order-lg-2 col-sm-12 content-area" id="everyware-theme-base-1">';
			}
		echo $html; // WPCS: XSS OK.
	} else {
	    echo '<div class="col-md-12 content-area" id="everyware-theme-base-1">';
	}