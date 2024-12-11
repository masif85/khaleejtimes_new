<?php

use USKit\Base\Parts\Concept;
use USKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Twig\View;
use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\ConceptPage;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;

$current_page = Page::current();

if ($current_page instanceof Page) {
	$conceptPages = Concept::createFromPost($current_page);
	$conceptPage = new ConceptPage($conceptPages);
	$uuid = $conceptPage->getViewData()['uuid'];
	$query = QueryBuilder::where('ConceptUuids', $uuid)->andIfProperty('Status', 'usable');

	$provider = OpenContentProvider::setup([
		'q'                      => $query->buildQueryString(),
		'contenttypes'           => ['Article'],
		'sort.indexfield'        => 'Pubdate',
		'sort.Pubdate.ascending' => 'false',
		'limit'                  => 20,
		'start'                  => 0,
	]);

	$articles = $provider->queryWithRequirements();
	$teasers = [];

	if (is_array($articles)) {
		foreach ($articles as $key => $article) {
			if ($article instanceof OcArticle) {
				$newsmlArticle = NewsMLArticle::createfromOcObject($article);
				$teaser = new Teaser($newsmlArticle);

				$teasers[] = $teaser->getViewData();
			}
		}
	}

	$conceptPage->setViewData('articles', $teasers);
}

get_header();

$sidebar_pos = get_theme_mod( 'sidebar_position_content' );

?>

<div class="wrapper concept-page" id="index-wrapper">

	<div class="container" id="content" tabindex="-1">

			<div class="row">

			<!-- Do the left sidebar check and opens the everyware-theme-base-1 div -->
			<?php 
			if ($sidebar_pos != 'none' ) {
				get_template_part( 'sidebar-templates/left-sidebar-check-content' ); 
			}
			?>

			<?php
				if ($current_page instanceof Page) {
					$conceptArray = $conceptPage->getViewData();

					$conceptArray['teaser_frontpage_length'] = get_theme_mod( 'teaser_frontpage_length' );
					$conceptArray['category_block_main_category_display'] = get_theme_mod( 'category_block_main_category_display' );
					$conceptArray['category_block_main_time_diff_display'] = get_theme_mod( 'category_block_main_time_diff_display' );
					$conceptArray['category_block_main_summary_display'] = get_theme_mod( 'category_block_main_summary_display' );
					$conceptArray['category_block_main_author_display'] = get_theme_mod( 'category_block_main_author_display' );
					$conceptArray['category_block_main_time_display'] = get_theme_mod( 'category_block_main_time_display' );
					$conceptArray['timezone_setting'] = get_theme_mod( 'timezone_setting' );
					$conceptArray['teaser_time_format'] = get_theme_mod('teaser_time_format' );
					$conceptArray['published_time_label'] = get_theme_mod('published_time_label' );
					$conceptArray['modified_time_label'] = get_theme_mod('modified_time_label' );
					$conceptArray['image_endpoint_url'] = get_theme_mod('image_endpoint_url' );

					View::render('@base/page/concept-page-example', $conceptArray);
				}
			?>

			<!-- Do the right sidebar check -->
			<?php 
			if ($sidebar_pos != 'none' ) {
				get_template_part( 'sidebar-templates/right-sidebar-check-content' ); 
			}
			?>

			</div><!-- .row -->
	</div><!-- Container end -->

</div><!-- Wrapper end -->

<?php get_footer(); ?>