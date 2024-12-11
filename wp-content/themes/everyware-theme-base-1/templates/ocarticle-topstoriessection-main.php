<?php
/**
 * Article Name: Top Stories Section - Main
 */

use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Twig\View;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));
    
    $teaserArray = $teaser->getViewData();

    $teaserArray['featured_top_image_position'] = get_theme_mod( 'featured_top_image_position' );
    $teaserArray['teaser_frontpage_length'] = get_theme_mod( 'teaser_frontpage_length' );
    $teaserArray['published_time_label'] = get_theme_mod('published_time_label' );
    $teaserArray['modified_time_label'] = get_theme_mod('modified_time_label' );
    $teaserArray['featured_top_main_show_text'] = get_theme_mod('featured_top_main_show_text' );
    $teaserArray['featured_top_main_show_author'] = get_theme_mod('featured_top_main_show_author' );
    $teaserArray['featured_top_main_show_since_published'] = get_theme_mod('featured_top_main_show_since_published' );
    $teaserArray['time_diff'] = human_time_diff(date('U'), strtotime($teaserArray['pubdate']));
    $teaserArray['image_endpoint_url'] = get_theme_mod('image_endpoint_url' );

    View::render('@base/teaser/topstories_section-main.twig', $teaserArray);
}