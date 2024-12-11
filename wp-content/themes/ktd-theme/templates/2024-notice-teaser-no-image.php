<?php
/**
 * Article Name: 2024 Notice teaser no image
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;

if ($article instanceof OcArticle) {
  $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));
$counter=2;
  View::render('@base/teasers/2024-notice-teaser-no-image.twig', array_replace($teaser->getViewData(), [
      'counter' => $counter
    ]));
}