<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

</div><!-- #closing the everyware-theme-base-1 container from /global-templates/left-sidebar-check.php -->

<?php $sidebar_pos = get_theme_mod( 'sidebar_position_homepage' ); ?>

<?php if ( 'right' === $sidebar_pos || 'both' === $sidebar_pos ) : ?>

  <?php get_template_part( 'sidebar-templates/sidebar-right-home' ); ?>

<?php endif; ?>
