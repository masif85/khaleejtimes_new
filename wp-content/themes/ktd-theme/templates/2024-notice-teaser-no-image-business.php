<?php
/**
 * Article Name: 2024 Notice teaser no image business
 */

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;

if ($article instanceof OcArticle) {
  $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));
  /* View::render('@base/teasers/2024-notice-teaser-no-image-business.twig', array_replace($teaser->getViewData(), [
      'counter' => 1
    ])); */

    View::render('@base/teasers/2024-notice-teaser-no-image-business.twig', $teaser->getViewData());

}