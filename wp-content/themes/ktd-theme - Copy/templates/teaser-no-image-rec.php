<?php
/**
 * Article Name: Recomended Teaser
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;

if ($article instanceof OcArticle) {
  $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));
    View::render('@base/teasers/notice-teaser-rec.twig', array_replace($teaser->getViewData(), [   
  ]));
}
