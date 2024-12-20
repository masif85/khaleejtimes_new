<?php

use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use KTDTheme\ViewModels\BasePage;

$currentPage = Page::current();

if ($currentPage instanceof Page) {

  $page = new BasePage($currentPage);

  $getSolrQuery       = $currentPage->getMeta('ew_solr_query', '');

  $provider = OpenContentProvider::setup( [
    'q'                      => $getSolrQuery,
    'contenttypes'           => [ 'Article' ],
    'sort.indexfield'        => 'Pubdate',
    'sort.Pubdate.ascending' => 'false',
    'limit'                  => 30,
    'Status'                 => 'usable'
  ] );

  $articles = $provider->queryWithRequirements();

  if ( is_array( $articles ) ) {
    add_filter( 'ew_content_container_fill', function ( $arr ) use ( $articles ) {
      $arr = array_merge( $arr, $articles );

      return $arr;
    } );
  }

  View::render('@base/main', array_replace($page->getViewData(), [
    'tealiumGroup' => 'page',
    'parentPostName' => get_the_title($currentPage->post_parent),
    'adGroup' => 'section' //section fallback adGroup
  ]));
}
