<?php
/**
 * Article Name: Featured Slider
 */

use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Twig\View;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));

    $teaserArray = $teaser->getViewData();
    $teaserArray['featured_slider_headline_location'] = get_theme_mod( 'featured_slider_headline_location' );
    $teaserArray['featured_slider_caption_display'] = get_theme_mod( 'featured_slider_caption_display' );
    $teaserArray['image_endpoint_url'] = get_theme_mod('image_endpoint_url' );

    View::render('@base/teaser/featured-slider.twig', $teaserArray);
}