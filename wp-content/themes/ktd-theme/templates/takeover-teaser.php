<?php
/**
 * Article Name: Takeover teaser
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;

if ($article instanceof OcArticle) {
  $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));

  View::render('@base/teasers/takeover-teaser.twig', $teaser->getViewData());
}