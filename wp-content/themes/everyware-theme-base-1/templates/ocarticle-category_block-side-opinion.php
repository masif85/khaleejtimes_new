<?php
/**
 * Article Name: Category Block - Side - Opinion
 */

use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Twig\View;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));

    $teaserArray = $teaser->getViewData();

    $teaserArray['teaser_frontpage_length'] = get_theme_mod( 'teaser_frontpage_length' );
    $teaserArray['teaser_frontpage_blocks_display'] = get_theme_mod( 'teaser_frontpage_blocks_display' );
    $teaserArray['category_block_opinion_side_category_display'] = get_theme_mod( 'category_block_opinion_side_category_display' );
    $teaserArray['category_block_opinion_side_time_diff_display'] = get_theme_mod( 'category_block_opinion_side_time_diff_display' );
    $teaserArray['category_block_opinion_side_summary_display'] = get_theme_mod( 'category_block_opinion_side_summary_display' );
    $teaserArray['category_block_opinion_side_author_display'] = get_theme_mod( 'category_block_opinion_side_author_display' );
    $teaserArray['category_block_opinion_side_time_display'] = get_theme_mod( 'category_block_opinion_side_time_display' );
    $teaserArray['timezone_setting'] = get_theme_mod( 'timezone_setting' );
    $teaserArray['teaser_time_format'] = get_theme_mod('teaser_time_format' );
    $teaserArray['published_time_label'] = get_theme_mod('published_time_label' );
    $teaserArray['modified_time_label'] = get_theme_mod('modified_time_label' );
    $teaserArray['image_endpoint_url'] = get_theme_mod('image_endpoint_url' );

    $timeDiff = human_time_diff(date('U'), strtotime($teaserArray['pubdate']));
    $teaserArray['time_diff'] = $timeDiff;


    View::render('@base/teaser/category-block-side-opinion.twig', $teaserArray);
}