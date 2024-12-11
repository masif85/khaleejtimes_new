<?php declare(strict_types=1);

/* Template Name: Contact Us */

use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\BasePage;

$currentPage = Page::current();

if ($currentPage instanceof Page) {

  $page               = new BasePage($currentPage);
  $getSolrQuery       = get_post_meta($currentPage->id, 'ew_solr_query', true);
	$id="";
	$type="";
	$player="";
	if(@$_GET['match']):
		$id=@$_GET['match'];
		$type="match";
		$player="";
	endif;
	if(@$_GET['team']):		
		$id=@$_GET['team'];
		$type="team";
		$player="";
	endif;
	if(@$_GET['player']):
		$id=@$_GET['team'];
		$player=@$_GET['player'];
		$type="player_profile";
	endif;

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

  View::render('@base/page/page-summary', array_replace($page->getViewData(), [
    'tealiumGroup' => $type.'_summary',
	  'id' =>$id,
	  'player'=>$player,
	  'tmgroup'=>'match_summary',
	  'type'=>$type
	 
  ]));
}
