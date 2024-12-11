<?php
/**
 * Article Name: Notice teaser with image no lazyload blue
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;

if ($article instanceof OcArticle) {
  $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));

  View::render('@base/teasers/notice-teaser-with-image-blue-no-lazyload.twig', $teaser->getViewData());
}

