<?php
/**
 * Article Name: Breaking News Blue Teaser
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;

if ($article instanceof OcArticle) {
  $teaser = new Teaser(NewsMLArticle::createfromOcObject($article),$css);
	
  View::render('@base/teasers/news-blue-teaser.twig', array_replace($teaser->getViewData(), [
    'text' => 'Breaking News'
  ]));
}
