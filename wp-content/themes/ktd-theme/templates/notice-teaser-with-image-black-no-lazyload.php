<?php
/**
 * Article Name: Notice teaser with image no lazyload black
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;

if ($article instanceof OcArticle) {
  $teaser = new Teaser(NewsMLArticle::createfromOcObject($article),$css);

  View::render('@base/teasers/notice-teaser-with-image-black-no-lazyload.twig', $teaser->getViewData());
}

