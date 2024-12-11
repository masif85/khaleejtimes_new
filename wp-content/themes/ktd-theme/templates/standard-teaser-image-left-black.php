<?php
/**
 * Article Name: Standard teaser - image left black
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;

if ($article instanceof OcArticle) {
  $teaser = new Teaser(NewsMLArticle::createfromOcObject($article),$css);

  View::render('@base/teasers/standard-teaser-image-left-black.twig', $teaser->getViewData());
}
