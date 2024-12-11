<?php
/**
 * Article Name: Standard teaser text overlay no lazyload
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;

if ($article instanceof OcArticle) {
  $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));

  View::render('@base/teasers/standard-teaser-text-overlay-no-lazyload.twig', $teaser->getViewData());
}