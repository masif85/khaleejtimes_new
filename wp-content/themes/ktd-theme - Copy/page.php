<?php

use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use KTDTheme\ViewModels\BasePage;

$currentPage = Page::current();

if ($currentPage instanceof Page) {

//$menu_args = array('menu' => '63' );
//wp_nav_menu( $menu_args ); 
/*
$menu_items = wp_get_nav_menu_items( '63' );
echo "<pre>";
print_r($menu_items);
exit;
foreach ( (array) $menu_items as $key => $menu_item ) {
    echo $title = $menu_item->title;
    $url = $menu_item->url;
}

exit;
*/
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
