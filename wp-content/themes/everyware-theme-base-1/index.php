<?php

use EwTools\Models\Page;
use EwTools\Metaboxes\EwPageMeta;
use Everyware\OpenContentProvider;
use EwTools\Twig\View;

$provider = OpenContentProvider::setup( [
    'contenttypes'           => [ 'Article' ],
    'sort.indexfield'        => 'Pubdate',
    'sort.Pubdate.ascending' => 'false',
    'limit'                  => 30,
    'q' => EwPageMeta::getMetaData(Page::current(), 'query')
] );

$articles = $provider->queryWithRequirements();

if( is_array( $articles ) ) {
    add_filter( 'ew_content_container_fill', function ( $arr ) use ( $articles ) {
        $arr = array_merge( $arr, $articles );
        
        return $arr;
    } );
}

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

$sidebar_pos = get_theme_mod( 'sidebar_position_homepage' );
?>

<div class="wrapper" id="index-wrapper">

	<?php if ($sidebar_pos == 'both') { 
		echo '<div class="sidebar_both">';
	} ?>

	<div class="container" id="content" tabindex="-1">
			<div class="row">

			<!-- Do the left sidebar check and opens the everyware-theme-base-1 div -->
			<?php get_template_part( 'global-templates/left-sidebar-check-homepage' ); ?>

				<div class="page-content">
					<div class="default_message">This is your default page.</div>
				</div><!-- .page-content -->
			
			
			<!-- Do the right sidebar check -->
			<?php get_template_part( 'global-templates/right-sidebar-check-homepage' ); ?>

			</div><!-- .row -->
	</div><!-- Container end -->

	<?php if ($sidebar_pos == 'both') { 
		echo '</div>';
	} ?>

</div><!-- Wrapper end -->

<?php get_footer(); ?>

