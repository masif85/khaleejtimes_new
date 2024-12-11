<?php
/**
 * Article Name: Category List - Rest
 */

use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Twig\View;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));

    View::render('@base//teaser/category-list-rest.twig', $teaser->getViewData());
}