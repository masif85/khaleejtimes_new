<?php
/**
 * Article Name: Teaser Image Right
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use EuKit\Base\ViewModels\Teaser;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article), $args ?? []);
    $teaser->setImageRatio('3:2');
    $teaser->setImageSizesData('66%');

    View::render('@base/teasers/article-image-right.twig', $teaser->getViewData());
}
