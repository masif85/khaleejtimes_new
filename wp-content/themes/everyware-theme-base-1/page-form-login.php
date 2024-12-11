<?php

use USKit\Base\ViewModels\BasePage;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Twig\View;
use USKit\Base\Teaser;
use USKit\Base\NewsMLArticle;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;

$current_page = Page::current();

if ($current_page instanceof Page) {

    $pageTemplate = new BasePage($current_page);

    $pageMetaData = get_post_meta($post->ID, 'ew_page_meta_box', true);

	if (isset($pageMetaData) == true ) {
		$pageMetaDataQuery = $pageMetaData;
	} else {
		$pageMetaDataQuery = false;
	}
	
	$provider = OpenContentProvider::setup( [
		'contenttypes'           => [ 'Article' ],
		'sort.indexfield'        => 'Pubdate',
		'sort.Pubdate.ascending' => 'false',
		'start'                  => isset($_GET['start']) ? (int)$_GET['start'] : 0,
		'q' => $pageMetaDataQuery
	] );

	$articles = $provider->queryWithRequirements();
	$articlesCount = count($articles);

	$teaserArray['teaser_time_display'] = get_theme_mod( 'teaser_time_display' );
	$teaserArray['timezone_setting'] = get_theme_mod( 'timezone_setting' );
	$teaserArray['teaser_time_format'] = get_theme_mod('teaser_time_format' );
	$teaserArray['published_time_label'] = get_theme_mod('published_time_label' );
	$teaserArray['modified_time_label'] = get_theme_mod('modified_time_label' );
	$teaserArray['teaser_section_front_length'] = get_theme_mod('teaser_section_front_length' );

	if( is_array( $articles ) ) {
		add_filter( 'ew_content_container_fill', function ( $arr ) use ( $articles ) {
			$arr = array_merge( $arr, $articles );
	
		   return $arr;
		} );
	}
}

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$sidebar_pos = get_theme_mod( 'sidebar_position_sectionfront' );
require get_theme_file_path( '/inc/set_variables/pagination_variables.php' );
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

				<header class="entry-header">
					<h1 class="entry-title">Login</h1>
				</header>


				<?php
				
				if ( is_user_logged_in() ) {
					echo 'You are already logged in. Click <a href="/">here</a> to go back to the homepage.';
				} else {
					wp_login_form();
				}
				?>

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
