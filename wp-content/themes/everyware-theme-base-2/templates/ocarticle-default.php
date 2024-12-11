<?php
/**
 * Article Name: Standard teaser
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use EuKit\Base\ViewModels\Teaser;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));

    View::render('@base/teasers/article-default.twig', $teaser->getViewData());
}
