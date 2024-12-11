<?php
/**
 * Article Name: Teaser Image Bottom
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use EuKit\Base\ViewModels\Teaser;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article), $args ?? []);

    View::render('@base/teasers/article-image-bottom.twig', $teaser->getViewData());
}
