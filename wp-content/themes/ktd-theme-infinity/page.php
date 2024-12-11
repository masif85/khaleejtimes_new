<?php

use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use KTDTheme\ViewModels\BasePage;

$currentPage = Page::current();

if ($currentPage instanceof Page) {
/*$menu_name = 'Top Menu (Blue menu)'; //menu slug
$locations = get_nav_menu_locations();
$menu = wp_get_nav_menu_object( $locations[ $menu_name ] );
$menuitems = wp_get_nav_menu_items( $menu->term_id, array( 'order' => 'DESC' ) );

echo "<pre>";
print_r($menuitems);
echo "</pre>";
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
