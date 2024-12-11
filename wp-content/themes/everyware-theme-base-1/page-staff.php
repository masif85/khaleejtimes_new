<?php

use USKit\Base\PageTemplate;
use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\Parts\Concept;
use USKit\Base\ViewModels\Teaser;
use USKit\Base\ViewModels\BaseObject;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;

if (isset($_GET['uuid'])) {
	$uuid = $_GET['uuid'];
} else {
	$uuid = NULL;
}

$staff_content = OpenContentProvider::setup( [
	'q'  => QueryBuilder::where('uuid', $uuid)->andIfProperty('Status', 'usable')->buildQueryString(),
] );

// Build contributor object.
$staff_content->setPropertyMap( 'Concept' );

$staffData = array_map( function ( Concept $concept ) {
    $concept_teaser = new BaseObject( $concept );
	return $concept_teaser->getViewData();
}, $staff_content->queryWithRequirements() );

$article_count = [
	'q'                      => QueryBuilder::where('ConceptAuthorUuids', $uuid)->andIfProperty('Status', 'usable')->buildQueryString(),
	'contenttypes'           => [ 'Article' ],
	'sort.indexfield'        => 'Pubdate',
	'limit'                  => 0,
	'sort.Pubdate.ascending' => 'false',
];

$oc = new OcAPI();
$articles = $oc->search($article_count, true);

$articleCount = $articles['hits'];

$article_content = OpenContentProvider::setup( [
	'q'                      => QueryBuilder::where('ConceptAuthorUuids', $uuid)->andIfProperty('Status', 'usable')->buildQueryString(),
	'contenttypes'           => [ 'Article' ],
	'sort.indexfield'        => 'Pubdate',
	'limit'                  => 10,
	'start'                  => isset($_GET['start']) ? (int)$_GET['start'] : 0,
	'sort.Pubdate.ascending' => 'false',
] );

// Build contributor's articles object.
$article_content->setPropertyMap( 'Article' );
$articles = $article_content->queryWithRequirements();

$articlesData = array_map( function ( NewsMLArticle $article ) {
    $teaser = new Teaser( $article );
	return $teaser->getViewData();
}, $article_content->queryWithRequirements() );
$currPage_articleCount = count($articlesData);

if(count($articlesData) > 0) {
	$articles = View::generate( '@base/page/staff/articles-list.twig', [
				'articles' => $articlesData,
				'image_endpoint_url' => get_theme_mod('image_endpoint_url' )
	]);
}

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$sidebar_pos = get_theme_mod( 'sidebar_position_sectionfront' );
require get_theme_file_path( '/inc/set_variables/pagination_variables.php' );
?>

<div class="wrapper" id="page-wrapper">
	<div class="container" id="staff-page">
		<div class="row">

			<?php 
				get_template_part( 'sidebar-templates/left-sidebar-check-content' ); 
			?>

			<main class="site-main" id="main">

				<?php

				if(count($staffData) > 0) {
					$staff = View::render( '@base/page/staff/staff.twig', [
								'staff' => $staffData,
								'image_endpoint_url' => get_theme_mod('image_endpoint_url' )
					]);
				}

                View::render( '@base/page/staff/articles.twig', [
                    'articles' => $articles,
				] );
				
				/* Variables set in /inc/set_variables/pagination_variables.php */
				View::render('@base/theme-templates/components/pagination-staff.twig', [
					'start_set' => $start_set,
					'start_value' => $start_value,
					'page_number' => $page_number,
					'page_request' => $page_request,
					'page_request_noquery' => $page_request_noquery,
					'previous_link' => $previous_link,
					'previous_start' => $previous_start,
					'next_link' => $next_link,
					'next_start' => $next_start,
					'articlesCount' => $articleCount,
					'currPage_articleCount' => $currPage_articleCount,
					'searchPage' => true,
					'uuid' => $uuid
				]);

				?>

			</main>

			<?php 
				get_template_part( 'sidebar-templates/right-sidebar-check-content' ); 
			?>

		</div><!-- .row -->
	</div><!-- Container end -->
</div><!-- Wrapper end -->

<?php get_footer(); ?>
