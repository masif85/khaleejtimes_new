<?php
/**
 * Article Name: Thin Teaser - Image Left
 */

use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Twig\View;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));
    $teaserArray = $teaser->getViewData();

    $teaserArray['teaser_frontpage_length'] = get_theme_mod( 'teaser_frontpage_length' );
    $teaserArray['timezone_setting'] = get_theme_mod( 'timezone_setting' );
    $teaserArray['teaser_time_format'] = get_theme_mod('teaser_time_format' );
    $teaserArray['published_time_label'] = get_theme_mod('published_time_label' );
    $teaserArray['modified_time_label'] = get_theme_mod('modified_time_label' );
    $teaserArray['time_diff'] = human_time_diff(date('U'), strtotime($teaserArray['pubdate']));
    $teaserArray['thin_teaser_category_display'] = get_theme_mod('thin_teaser_category_display' );
    $teaserArray['thin_teaser_time_diff_display'] = get_theme_mod('thin_teaser_time_diff_display' );
    $teaserArray['thin_teaser_summary_display'] = get_theme_mod('thin_teaser_summary_display' );
    $teaserArray['thin_teaser_time_display'] = get_theme_mod('thin_teaser_time_display' );
    $teaserArray['thin_teaser_author_display'] = get_theme_mod('thin_teaser_author_display' );
    $teaserArray['image_endpoint_url'] = get_theme_mod('image_endpoint_url' );

    View::render('@base/teaser/thin-teaser-image-left.twig', $teaserArray);
}