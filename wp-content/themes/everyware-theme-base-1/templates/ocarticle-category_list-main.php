<?php
/**
 * Article Name: Category List - Main
 */

use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Twig\View;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));
    $teaserArray = $teaser->getViewData();

    $teaserArray['image_endpoint_url'] = get_theme_mod('image_endpoint_url' );

    View::render('@base//teaser/category-list-main.twig', $teaserArray);
}
