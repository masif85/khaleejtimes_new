<?php
/**
 * Article Name: tabs-teaser-frontpage-nf
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;

if ($article instanceof OcArticle) {
  $teaser = new Teaser(NewsMLArticle::createfromOcObject($article),$css);

  View::render('@base/teasers/tabs-teaser-frontpage-nf.twig', $teaser->getViewData());
}

