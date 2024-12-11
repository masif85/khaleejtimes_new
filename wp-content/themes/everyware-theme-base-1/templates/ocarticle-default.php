<?php
/**
 * Article Name: Standard
 */

use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Twig\View;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));
    $teaserArray = $teaser->getViewData();

    //$teaserArray['variable_name'] = get_theme_mod( 'variable_name' );

    View::render('@base/teaser/default.twig', $teaserArray );
}