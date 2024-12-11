<?php
/**
 * Article Name: Teaser-image top
 */

use Customer\TeaserArticle;
use EwTools\Twig\View;

$teaser = TeaserArticle::createfromOcObject( $article );
$teaser->appendClass('teaser--image_top');

View::render( '@base//teaser/image-top.twig', $teaser->getViewData() );
