<?php
/**
 * Article Name: Notice teaser image bottom
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;

if ($article instanceof OcArticle) {
  $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));

  View::render('@base/teasers/notice-teaser-image-bottom.twig', $teaser->getViewData());
}