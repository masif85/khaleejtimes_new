<?php
/**
 * Article Name: Side by side teaser no lazyload
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;

if ($article instanceof OcArticle) {
  $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));

  View::render('@base/teasers/side-by-side-teaser-no-lazyload.twig', $teaser->getViewData());
}