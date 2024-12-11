<?php
/**
 * Article Name: Right now
 */

use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Twig\View;

$teaser = new Teaser(NewsMLArticle::createfromOcObject($article));

View::render( '@base//teaser/list.twig', $teaser->getViewData() );