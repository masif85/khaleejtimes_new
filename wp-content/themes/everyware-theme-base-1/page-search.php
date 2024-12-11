<?php

use USKit\Base\PageTemplate;
use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;

$sortings = [
    'desc' => 'desc',
    'asc' => 'asc'
];

if (isset($_GET['q'])) {
	$query = $_GET['q'];
} else {
	$query = NULL;
}

if (isset($_GET['sort'])) {
	$sort = $_GET['sort'];
} else {
	$sort = NULL;
}

if (isset($_GET['category'])) {
	$categoryFilter = $_GET['category'];
} else {
	$categoryFilter = NULL;
}

if ($sort == 'asc') {
	$selectedSort = 'sort.Pubdate.ascending';
} elseif ($sort == 'desc' ) {
	$selectedSort = 'sort.Pubdate.descending';
} else {
	$selectedSort = 'sort.Pubdate.ascending';
}

$nextSort = $sort === $sortings['desc'] ? $sortings['asc'] : $sortings['desc'];

if ($categoryFilter == NULL ) {
	$provider_count = [
		'q'						 => QueryBuilder::where('RelatedConceptCategoriesName', [ucwords($categoryFilter)])->andIfProperty('Status', 'usable')->setText(urldecode($query))->buildQueryString(),
		'contenttypes'           => [ 'Article' ],
		'sort.indexfield'        => 'Pubdate',
		$selectedSort			 => true,
		'limit'                  => 0,
		'Status'				 => 'usable'
	];
} else {
	$provider_count = [
		'q'						 => QueryBuilder::where('RelatedConceptCategoriesName', [ucwords($categoryFilter)])->andIfProperty('Status', 'usable')->setText(urldecode($query))->buildQueryString(),
		'contenttypes'           => [ 'Article' ],
		'sort.indexfield'        => 'Pubdate',
		$selectedSort			 => true,
		'limit'                  => 0,
		'Status'				 => 'usable'
	];	
}

$oc = new OcAPI();
$articles = $oc->search($provider_count, true);

$articleCount = $articles['hits'];

if ($categoryFilter == NULL ) {
	$provider = OpenContentProvider::setup( [
		'q'                      => QueryBuilder::where('Status', 'usable')->setText(urldecode($query))->buildQueryString(),
		'contenttypes'           => [ 'Article' ],
		'sort.indexfield'        => 'Pubdate',
		$selectedSort			 => true,
		'limit'                  => 10,
		'start'                  => isset($_GET['start']) ? (int)$_GET['start'] : 0
	] );
} else {
	$provider = OpenContentProvider::setup( [
		'q'						 => QueryBuilder::where('RelatedConceptCategoriesName', [ucwords($categoryFilter)])->andIfProperty('Status', 'usable')->setText(urldecode($query))->buildQueryString(),
		'contenttypes'           => [ 'Article' ],
		'sort.indexfield'        => 'Pubdate',
		$selectedSort			 => true,
		'limit'                  => 10,
		'start'                  => isset($_GET['start']) ? (int)$_GET['start'] : 0,
		'Status'				 => 'usable'
	] );
}

$provider->setPropertyMap( 'Article' );

$articlesData = array_map( function ( NewsMLArticle $article ) {
    $teaser = new Teaser( $article );
	return $teaser->getViewData();
}, $provider->queryWithRequirements() );

$articles = null;
$currPage_articleCount = count($articlesData);

$articleArray = $provider->queryWithRequirements();

/* Return all categories from searched stories and returned in an array to be used as a filter. */
$searchCategoryArray = [];
foreach ($articleArray as $article){
	$articleCategoryArray = $article['categories'];

	foreach ($articleCategoryArray as $category ) {
		if(isset($category)){
			$category = $category['name'];

			if (!in_array($category, $searchCategoryArray)) {
				array_push($searchCategoryArray, $category);
			}
		}
	}
}
sort($searchCategoryArray);

$image_endpoint_url = get_theme_mod('image_endpoint_url' );
if(count($articlesData) > 0) {
   $articles = View::generate( '@base/search/articles-list.twig', [
			   'articles' => $articlesData,
			   'image_endpoint_url' => $image_endpoint_url
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
	<div class="container" id="search-page">

		<div class="row">

			<?php 
			if ( $sidebar_pos == 'right' || $sidebar_pos == 'left' || $sidebar_pos == 'both' ) {
				get_template_part( 'sidebar-templates/left-sidebar-check-sectionfront' ); 
			}
			?>

			<main class="site-main" id="main">

            <?php
                View::render( '@base/page/search', [
                    'articles' => $articles,
                    'query' => $query,
					'sort' => $sort,
					'articleCount' => $articleCount,
					'searchCategoryArray' => $searchCategoryArray,
					'categoryFilter' => $categoryFilter
                ] );

				/* Variables set in /inc/set_variables/pagination_variables.php */
				View::render('@base/theme-templates/components/pagination.twig', [
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
					'query' => $query
				]);
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
