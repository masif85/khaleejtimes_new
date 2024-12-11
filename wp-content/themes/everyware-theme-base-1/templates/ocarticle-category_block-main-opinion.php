<?php
/**
 * Article Name: Category Block - Main - Opinion
 */

use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Twig\View;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));
    $teaserArray = $teaser->getViewData();

    $teaserArray['teaser_frontpage_length'] = get_theme_mod( 'teaser_frontpage_length' );
    $teaserArray['category_block_opinion_main_category_display'] = get_theme_mod( 'category_block_opinion_main_category_display' );
    $teaserArray['category_block_opinion_main_time_diff_display'] = get_theme_mod( 'category_block_opinion_main_time_diff_display' );
    $teaserArray['category_block_opinion_main_summary_display'] = get_theme_mod( 'category_block_opinion_main_summary_display' );
    $teaserArray['category_block_opinion_main_author_display'] = get_theme_mod( 'category_block_opinion_main_author_display' );
    $teaserArray['category_block_opinion_main_time_display'] = get_theme_mod( 'category_block_opinion_main_time_display' );
    $teaserArray['timezone_setting'] = get_theme_mod( 'timezone_setting' );
    $teaserArray['teaser_time_format'] = get_theme_mod('teaser_time_format' );
    $teaserArray['published_time_label'] = get_theme_mod('published_time_label' );
    $teaserArray['modified_time_label'] = get_theme_mod('modified_time_label' );
    $teaserArray['time_diff'] = human_time_diff(date('U'), strtotime($teaserArray['pubdate']));
    $teaserArray['image_endpoint_url'] = get_theme_mod('image_endpoint_url' );


    View::render('@base/teaser/category-block-main-opinion.twig', $teaserArray);
}