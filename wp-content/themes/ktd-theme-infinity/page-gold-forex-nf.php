<?php declare(strict_types=1);

/* Template Name: Gold Forex NF */

use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\BasePage;

$currentPage = Page::current();

if ($currentPage instanceof Page) {
	$subpage= strtolower(basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
	if($subpage=='gold-forex')
	{
	$subpage="UAE";	
	}
	
  $page = new BasePage($currentPage);
  $getSolrQuery = get_post_meta($currentPage->id, 'ew_solr_query', true);

  $menu = [
    'gold-forex' => 'UAE',
    'bahrain'    => 'Bahrain',
    'qatar'      => 'Qatar'
  ];

  $goldforexlist = [
    'uae-draft'      => 'b79e4450-81f2-46ec-8752-6c2ff8e37890',
    'uae-gold'       => 'a2344da1-7204-4131-9f2c-1b94a6688cbd',
    'uae-silver'     => '966d14a5-be5b-484b-958c-5cebd45042cb',
    'bahrain-draft'  => 'fa942856-2f1c-4b26-8ad5-1032e3ccd67b',
    'qatar-draft'    => '3b75ea09-8a5c-46d8-a11a-318ace11b1a2'
  ];

  $page->setViewData('goldforex', [
    'menu' => $menu,
    'forex' => $goldforexlist
  ]);


  // get goldforex objects
  $provider = OpenContentProvider::setup([
      'q' => QueryBuilder::query('CustomerContentSubType:GOLDFOREX')->buildQueryString(),
      'contenttype' => 'Article',
      'Status' => 'usable'
    ]
  );

  $provider->setPropertyMap('Article');
  $articles     = $provider->queryWithRequirements();
  $array        = '';
  $counter      = 0;

  $articles = RearrangeArticles($goldforexlist, $articles);
  if(count($articles) > 0)
  {
    foreach ($articles as $item) {
      if ($item['body_raw'])
      {
        $xml  = simplexml_load_string($item['body_raw']);
        $json = json_encode($xml->group->children());
        $array = json_decode($json,TRUE);
        $articles[$counter]['forextable'] = $array;
        $articles[$counter]['timeago']    = $item->getPubDate()->diffForHumans();

      }
      $counter++;
    }
  }

  // get exchange rates
  $provider = OpenContentProvider::setup([
      'contenttype' => 'CustomerExchangeRate'
    ]
  );

  $provider->setPropertyMap('CustomerExchangeRate');
  $exchangerate  = $provider->queryWithRequirements();

  //Content containers only display exchang rates
  if( is_array( $exchangerate ) ) {
    add_filter( 'ew_content_container_fill', function ( $arr ) use ( $exchangerate ) {
      $arr = array_merge( $arr, $exchangerate );

      return $arr;
    } );
  }

  $EXRateArr = "";
  $EXRateUpdArr = [
    'updated',
    'timeago'
  ];

  if(count($exchangerate) > 0)
  {
    if ($exchangerate[0]['forexBody'])
    if ($exchangerate[0]['forexBody'])
    {
      $xml  = simplexml_load_string($exchangerate[0]['forexBody']);
      $json = json_encode($xml->children());
      $EXRateArr = json_decode($json,TRUE);
    }

    if ($exchangerate[0]['updated'])
    {
      $EXRateUpdArr['updated']  = $exchangerate[0]['updated'];
      $EXRateUpdArr['timeago']  = timeago($exchangerate[0]['updated']);
    }
  }


  View::render('@base/page/page-gold-forex-nf', array_replace($page->getViewData(), [
    'articles' => $articles,
    'pagename' => $currentPage->post_name,
    'ParentSlug' => get_post_field( 'post_name', $currentPage->post_parent ),
    'metaGroup' => 'goldforex',
    'tealiumGroup' => 'gold-forex',
    'adGroup' => 'gold-forex',
	 'subpage' => $subpage,
    'exchangerate' => $EXRateArr,
    'exchangerateUpd' => $EXRateUpdArr
  ]));

}

function RearrangeArticles($PArticlesFromList, $PArticles) {

  $ArrArticlesFromList = $PArticlesFromList;

  $ListOrderArticles = array();
  $ListCounter       = 0;

  // loop trough each uuid from the correct list order, and if there is a match, place it in the array
  foreach ($ArrArticlesFromList as $ArticleUuid) {
    foreach ($PArticles as $temparticle) {
      if ($ArticleUuid == $temparticle['uuid']) {
        $ListOrderArticles[$ListCounter] = $temparticle;
      }
    }
    $ListCounter++;
  }
  return $ListOrderArticles;
}
function timeago($date) {
  $timestamp = strtotime($date);

  $strTime = array("second", "minute", "hour", "day", "month", "year");
  $length = array("60","60","24","30","12","10");

  $currentTime = time();
  if($currentTime >= $timestamp) {
    $diff     = time()- $timestamp;
    for($i = 0; $diff >= $length[$i] && $i < count($length)-1; $i++) {
      $diff = $diff / $length[$i];
    }

    $diff = round($diff);
    return $diff . " " . $strTime[$i] . "(s) ago ";
  }
}