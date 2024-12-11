<?php
/**
 * Article Name: Teaser - Simple
 */

use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Twig\View;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));

    $teaserArray = $teaser->getViewData();

    //$teaserArray['teaser_frontpage_length'] = get_theme_mod( 'teaser_frontpage_length' );

    View::render('@base/teaser/teaser-simple.twig', $teaserArray);
}