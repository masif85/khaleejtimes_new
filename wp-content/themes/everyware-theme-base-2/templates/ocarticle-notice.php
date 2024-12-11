<?php
/**
 * Article Name: Notice
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use EuKit\Base\ViewModels\Teaser;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));
    $viewData = $teaser->getViewData();
    unset($viewData['image']);
    View::render('@base/teasers/article-notice.twig', $viewData);
}
