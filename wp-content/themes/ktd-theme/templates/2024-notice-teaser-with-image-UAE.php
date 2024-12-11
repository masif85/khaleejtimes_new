<?php
/**
 * Article Name: 2024 Notice teaser with image UAE
 */
use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;

if ($article instanceof OcArticle) {
  $teaser = new Teaser(NewsMLArticle::createfromOcObject($article));

  View::render('@base/teasers/2024-notice-teaser-with-image-UAE.twig', $teaser->getViewData());
}