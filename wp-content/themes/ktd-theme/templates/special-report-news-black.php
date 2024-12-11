<?php
/**
 * Article Name: Special Report News Black Teaser
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;

if ($article instanceof OcArticle) {
  $teaser = new Teaser(NewsMLArticle::createfromOcObject($article),$css);

  View::render('@base/teasers/news-black-teaser.twig', array_replace($teaser->getViewData(), [
    'text' => 'Special Report'
  ]));	
	
}
