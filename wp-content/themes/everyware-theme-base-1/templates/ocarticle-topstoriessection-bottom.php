<?php
/**
 * Article Name: Top Stories Section - Bottom
 */

use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Twig\View;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));

    $teaserArray = $teaser->getViewData();

    $teaserArray['featured_top_bottom_image_position'] = get_theme_mod( 'featured_top_bottom_image_position' );
    $teaserArray['image_endpoint_url'] = get_theme_mod('image_endpoint_url' );

    View::render('@base/teaser/topstories_section-bottom.twig', $teaserArray);
}