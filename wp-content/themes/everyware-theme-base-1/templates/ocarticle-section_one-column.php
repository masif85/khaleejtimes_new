<?php
/**
 * Article Name: Section - One Column
 */

use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Twig\View;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));

    $teaserArray = $teaser->getViewData();

    $teaserArray['teaser_time_display'] = get_theme_mod( 'teaser_time_display' );
    $teaserArray['timezone_setting'] = get_theme_mod( 'timezone_setting' );
    $teaserArray['teaser_time_format'] = get_theme_mod('teaser_time_format' );
    $teaserArray['published_time_label'] = get_theme_mod('published_time_label' );
    $teaserArray['modified_time_label'] = get_theme_mod('modified_time_label' );
    $teaserArray['teaser_section_front_length'] = get_theme_mod('teaser_section_front_length' );
    $teaserArray['section_teaser_one_column_category_overlay_display'] = get_theme_mod('section_teaser_one_column_category_overlay_display' );
    $teaserArray['image_endpoint_url'] = get_theme_mod('image_endpoint_url' );

    View::render('@base/teaser/section-teaser-one-column.twig', $teaserArray);
}