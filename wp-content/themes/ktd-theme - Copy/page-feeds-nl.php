<?php

use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Twig\View;

header('Content-Type: application/xml; charset=utf-8');

$protocol           = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$current_domain     = $protocol . $_SERVER['HTTP_HOST'];
$options            = get_option( 'oc_options' );
$image_endpoint_url = $options['imengine_url'];

$current_page = Page::current();

$template     = "newsletter-";

//preperation for queries
$NewsLetterArr               = array();

// Newsletter Topic
$NewsLetterArr[0]['uuid']    = '98607a39-e1a9-4323-80e6-6e4c1877e558';
$NewsLetterArr[0]['limit']   = '15';
$NewsLetterArr[0]['subtype'] = 'ARTICLE';
$NewsLetterCounter = 0;
$articles = array();

// get all articles and save the result in array
foreach ($NewsLetterArr as $NewsLetter) {

  $provider = OpenContentProvider::setup( [
    'ConceptUuids' => $NewsLetter['uuid'],
    'contenttype' => 'Article',
    'CustomerContentSubType' => $NewsLetter['subtype'],
    'sort.indexfield'        => 'Pubdate',
    'sort.Pubdate.ascending' => true,
    'limit' => $NewsLetter['limit'],
    'start' => 0,
    'properties' => ['CustomerVideoId'],
    'Status' => 'usable'
  ] );

  $provider->setPropertyMap('Article');

  if (count($provider->queryWithRequirements()) > 0) {
    $tarticles = $provider->queryWithRequirements();

    // for each OC result and each object, save it in the $articles array
    foreach ($tarticles as $tarticle) {
      $articles[$NewsLetterCounter] = $tarticle;
      $NewsLetterCounter++;
    }
  }
}

//Content containers only display Articles
if ( is_array( $articles ) ) {
  add_filter( 'ew_content_container_fill', function ( $arr ) use ( $articles ) {
    $arr = array_merge( $arr, $articles );

    return $arr;
  } );
}

// if there is content, genereate each content
if (count($articles) > 0) {
  $articles = View::generate( '@base/feeds/'.$template.'item.twig', [
    'articles' => $articles,
    'image_endpoint_url' => $image_endpoint_url,
    'current_domain' => $current_domain,
  ]);
}

if( is_array( $articles ) ){
  $articles = '';
}


$content = get_the_content( null, false );
$content = apply_filters( 'the_content', $content );
$content = str_replace( ']]>', ']]&gt;', $content );
$content = str_replace( '<p>', '', $content );
$content = str_replace( '</p>', '', $content );
$content = str_replace( '<br />', '', $content );



// render the final template
View::render( '@base/feeds/'.$template.'items.twig', [
    'articles' => $articles,
    'current_domain' => $current_domain,
    'pagecontent' => $content,
    'pagename' => $current_page->post_name
] );