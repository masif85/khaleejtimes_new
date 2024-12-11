<?php
/**
 * Article Name: Notice with Image
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use EuKit\Base\ViewModels\Teaser;

if ($article instanceof OcArticle) {
    $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));
    View::render('@base/teasers/article-notice.twig', $teaser->getViewData());
}
