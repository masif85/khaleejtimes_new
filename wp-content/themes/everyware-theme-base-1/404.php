<?php

use EwKit\Theme\PageNotFoundTemplate;
use Infomaker\Everyware\Twig\View;

//$page = new PageNotFoundTemplate();
$sidebar_pos = get_theme_mod( 'sidebar_position_sectionfront' );

get_header();

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrapper" id="page-wrapper">
	<div class="container" id="section-front">
		<div class="row">

			<?php 
			if ( $sidebar_pos == 'right' || $sidebar_pos == 'left' || $sidebar_pos == 'both' ) {
				get_template_part( 'sidebar-templates/left-sidebar-check-sectionfront' ); 
			}
			?>

			<main class="site-main" id="main">
            
            <div class="pagenotfound_title">Page Not Found</div>
            <div class="pagenotfound_message">The page you are looking for does not exist.</p>

			</main>

			<?php 
				if ( $sidebar_pos == 'right' || $sidebar_pos == 'left' || $sidebar_pos == 'both' ) {
					get_template_part( 'sidebar-templates/right-sidebar-check-sectionfront' ); 
				}
			?>

		</div><!-- .row -->
	</div><!-- Container end -->
</div><!-- Wrapper end -->

<?php get_footer(); ?>
