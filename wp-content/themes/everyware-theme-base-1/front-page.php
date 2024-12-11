<?php

use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\Parts\Teaser;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Twig\View;
use USKit\Base\ViewModels\BasePage;

$current_page = Page::current();
$sidebar_pos = get_theme_mod( 'sidebar_position_homepage' );
$breaking_news_switch = get_theme_mod( 'breaking_news_switch' );
$breaking_news_template = get_theme_mod( 'breaking_news_template' );
$breaking_news_time_limit = get_theme_mod( 'breaking_news_time_limit' );
$image_endpoint_url = get_theme_mod('image_endpoint_url' );

if ($current_page instanceof Page) {

    $pageTemplate = new BasePage($current_page);

    $provider = OpenContentProvider::setup( [
        'contenttypes'           => [ 'Article' ],
        'sort.indexfield'        => 'Pubdate',
        'sort.Pubdate.ascending' => 'false',
        'limit'                  => 30
    ] );

    $articles = $provider->queryWithRequirements();
    if( is_array( $articles ) ) {
        add_filter( 'ew_content_container_fill', function ( $arr ) use ( $articles ) {
            $arr = array_merge( $arr, $articles );

            return $arr;
        } );
    }
}

/* BREAKING NEWS SECTION */
if ($breaking_news_switch == 'on') {
	$to = date('Y/m/d h:iA');
	$to = date('Y/m/d h:iA',strtotime('+6 hour',strtotime($to)));
	$to = QueryBuilder::createOcDate($to);
	$from = date('Y/m/d h:iA',strtotime('-' . $breaking_news_time_limit . ' hour',strtotime($to)));
	$from = QueryBuilder::createOcDate($from);
	
	if ($breaking_news_time_limit != 0 ) {
		$dateSpan = '[' . $from . ' TO ' . $to . ']';
	} else {
		$dateSpan = '[1970-01-01T01:00:00Z TO ' . $to . ']';
	}

	$breaking_news_query = OpenContentProvider::setup( [
		'contenttypes'           => [ 'Article' ],
		'sort.indexfield'        => 'Pubdate',
		'sort.Pubdate.ascending' => 'false',
		'RelatedConceptCategoriesName' => 'Breaking',
		'limit'                  => 1,
		'Pubdate'				 => $dateSpan
	] );

	$breaking_news_query->setPropertyMap( 'Article' );

	$breakingCount = $breaking_news_query->queryWithRequirements();
	$breakingCount = count($breakingCount);

	$breakingData = array_map( function ( NewsMLArticle $article ) {
		$breaking_teaser = new Teaser( $article );
		return $breaking_teaser->getViewData();
	}, $breaking_news_query->queryWithRequirements() );

	if(count($breakingData) > 0) {
		if ($breaking_news_template == 'full_length') {
			$breakingArticles = View::generate( '@base/breaking/breakingArticles_fullLength.twig', [
						'articles' => $breakingData,
						'image_endpoint_url' => $image_endpoint_url
			]);
		} else {
			$breakingArticles = View::generate( '@base/breaking/breakingArticles_teaser.twig', [
				'articles' => $breakingData,
				'image_endpoint_url' => $image_endpoint_url
		]);
		}
	} else {
		$breakingArticles = false;
	}
}

if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

get_header();
?>

<?php if ( $breaking_news_switch == 'on' && $breaking_news_template == 'full_length' && $breakingCount != 0) : ?>
	<div class="breaking_fullLength">
		<?php View::render( '@base/breaking/breaking_fullLength', [ 'articles' => $breakingArticles ] ); ?>
	</div>
<?php endif; ?>

<div class="wrapper" id="index-wrapper">

	<?php if ($sidebar_pos == 'both') : ?>
		<div class="sidebar_both">
	<?php endif; ?>

	<div class="container" id="frontpage">
			<div class="row">

			<?php if ( $breaking_news_switch == 'on' && $breaking_news_template == 'teaser' && $breakingCount != 0) : ?>
				<div class="breaking_teaser">
					<?php View::render( '@base/breaking/breaking_teaser', [ 'articles' => $breakingArticles ] ); ?>
				</div>
			<?php endif; ?>

			<?php 
			if ($sidebar_pos != 'none' ) {
				get_template_part( 'sidebar-templates/left-sidebar-check-homepage' ); 
			}
			?>

			<?php View::render('@base/main', $pageTemplate->getViewData()); ?>
			
			<?php 
			if ($sidebar_pos != 'none' ) {
				get_template_part( 'sidebar-templates/right-sidebar-check-homepage' ); 
			}
			?>

			</div><!-- .row -->
	</div><!-- Container end -->

	<?php if ($sidebar_pos == 'both') : ?>
		</div>
	<?php endif; ?>

</div><!-- Wrapper end -->

<?php get_footer(); ?>

