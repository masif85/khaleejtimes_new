<?php
/**
 * Article Name: videos podcast widget New home layout 
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;

if ($article instanceof OcArticle) {
  $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));

  View::render('@base/teasers/notice-teaser-with-image-video-newhome.twig', $teaser->getViewData());
}

