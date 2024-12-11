<?php
use Infomaker\Everyware\Twig\View;
use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\ArticlePage;
use Infomaker\Everyware\Base\Models\Post;

$articlePost = Post::createFromPost($post);
$sidebar_pos = get_theme_mod( 'sidebar_position_content' );

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();
?>

<div class="wrapper" id="index-wrapper">
	<div class="container" id="content" tabindex="-1">
		<div class="row">

			<!-- Do the left sidebar check and opens the everyware-theme-base-1 div -->
			<?php 
				if ($sidebar_pos != 'none' ) {
					get_template_part( 'sidebar-templates/left-sidebar-check-content' ); 
				}
			?>

			<?php
				if ($articlePost instanceof Post) {
                    $articlePage = new ArticlePage(NewsMLArticle::createFromPost($articlePost));

					$articleArray = $articlePage->getViewData();

					$articleArray['timezone_setting'] = get_theme_mod( 'timezone_setting' );
					$articleArray['published_time_label'] = get_theme_mod('published_time_label' );
					$articleArray['modified_time_label'] = get_theme_mod('modified_time_label' );
					$articleArray['article_time_format'] = get_theme_mod('article_time_format' );
					$articleArray['article_ad_switch'] = get_theme_mod('article_ad_switch');
					$articleArray['article_ad_frequency'] = get_theme_mod('article_ad_frequency');
					$articleArray['article_ad_id'] = get_theme_mod('article_ad_id');
					$articleArray['article_image_width'] = get_theme_mod('article_image_width');
					$articleArray['article_image_ratio'] = get_theme_mod('article_image_ratio');
					$articleArray['use_disqus_comments'] = get_theme_mod('use_disqus_comments');
					$articleArray['disqus_site_id'] = get_theme_mod('disqus_site_id');
					$articleArray['image_endpoint_url'] = get_theme_mod('image_endpoint_url' );

					View::render('@base/article-page', $articleArray);
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





