<?php declare(strict_types=1);

/* Template Name: Advertise */

use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\BasePage;

$currentPage = Page::current();

if ($currentPage instanceof Page) {

  $page               = new BasePage($currentPage);
  $getSolrQuery       = get_post_meta($currentPage->id, 'ew_solr_query', true);

  $provider = OpenContentProvider::setup( [
    'q'                      => QueryBuilder::query($getSolrQuery)->buildQueryString(),
    'contenttypes'           => [ 'Article' ],
    'sort.indexfield'        => 'Pubdate',
    'sort.Pubdate.ascending' => 'false',
    'limit'                  => 12,
    'Status'                 => 'usable'
  ] );

  $articles = $provider->queryWithRequirements();

  if ( is_array( $articles ) ) {
    add_filter( 'ew_content_container_fill', static function ( $arr ) use ( $articles ) {
      return array_merge( $arr, $articles );
    } );
  }

  View::render('@base/page/page-static-advertise', array_replace($page->getViewData(), [
    'tealiumGroup' => 'advertise',
	  'pagename' => 'advertise'
  ]));
}