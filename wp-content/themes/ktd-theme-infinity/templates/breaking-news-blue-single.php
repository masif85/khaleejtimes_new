<?php
/**
 * Article Name: Breaking News Blue Teaser Single
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;

if ($article instanceof OcArticle) {
  $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));
	
  View::render('@base/teasers/news-blue-teaser-single.twig', array_replace($teaser->getViewData(), [
    'text' => 'Breaking News'
  ]));
}