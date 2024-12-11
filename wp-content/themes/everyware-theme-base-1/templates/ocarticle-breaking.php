<?php
/**
 * Article Name: Breaking
 */

use Customer\TeaserArticle;
use EwTools\Twig\View;

$teaser = TeaserArticle::createFromOcArticle( $article );
$teaser->appendClass('teaser--breaking');

View::render( '@base//teaser/breaking.twig', $teaser->getViewData() );
