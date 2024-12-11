<?php
/**
 * Article Name: Teaser Image Top
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use EuKit\Base\ViewModels\Teaser;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article), $args ?? []);

    View::render('@base/teasers/article-image-top.twig', $teaser->getViewData());
}
