<?php
/**
 * Article Name: Teaser-image left
 */

use Customer\TeaserArticle;
use EwTools\Twig\View;

$teaser = TeaserArticle::createfromOcObject( $article );
$teaser->appendClass('teaser--image_left');

View::render( '@base//teaser/image-left.twig', $teaser->getViewData() );
