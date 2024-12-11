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
$template     = "latest-news-";

//preperation for queries
$NewsLetterArr               = array();

//UAE section
$NewsLetterArr[0]['uuid']    = 'A972DD74-0236-4162-BF80-31D96DE6490D';
$NewsLetterArr[0]['limit']   = '50';
$NewsLetterArr[0]['subtype'] = 'ARTICLE';
//World section
$NewsLetterArr[1]['uuid']    = 'F7647EED-61BF-4444-98CB-8B28E185083E';
$NewsLetterArr[1]['limit']   = '50';
$NewsLetterArr[1]['subtype'] = 'ARTICLE';
//Business section
$NewsLetterArr[2]['uuid']    = '1DB7519B-DFB8-4102-8094-F4674422C664';
$NewsLetterArr[2]['limit']   = '50';
$NewsLetterArr[2]['subtype'] = 'ARTICLE';
//sports section
$NewsLetterArr[3]['uuid']    = '09D69DC2-9CFB-4DF9-B1D6-74E83EBB4BC7';
$NewsLetterArr[3]['limit']   = '50';
$NewsLetterArr[3]['subtype'] = 'ARTICLE';
//Tech section
$NewsLetterArr[4]['uuid']    = 'F3F784E9-3249-49E7-90B6-2F1661290F5F';
$NewsLetterArr[4]['limit']   = '50';
$NewsLetterArr[4]['subtype'] = 'ARTICLE';
//Entertainment section
$NewsLetterArr[5]['uuid']    = '7CF70BE9-50EC-42E9-96DC-2E809FC202B0';
$NewsLetterArr[5]['limit']   = '50';
$NewsLetterArr[5]['subtype'] = 'ARTICLE';
//Opinion section
$NewsLetterArr[6]['uuid']    = 'BB1BA6E4-8BF7-4D8E-A388-21D85EB1FE11';
$NewsLetterArr[6]['limit']   = '50';
$NewsLetterArr[6]['subtype'] = 'ARTICLE';
//city times section
$NewsLetterArr[7]['uuid']    = 'CB3617E3-7A40-41F5-9E59-C01A9B3076A9';
$NewsLetterArr[7]['limit']   = '50';
$NewsLetterArr[7]['subtype'] = 'ARTICLE';
//wknd section
$NewsLetterArr[8]['uuid']    = 'DCA8F478-606B-4FA2-8F10-103402C72633';
$NewsLetterArr[8]['limit']   = '50';
$NewsLetterArr[8]['subtype'] = 'ARTICLE';
//coronavirus pandemic section
$NewsLetterArr[9]['uuid']    = 'A7F50D98-8761-4D23-909B-A6324E735691';
$NewsLetterArr[9]['limit']   = '50';
$NewsLetterArr[9]['subtype'] = 'ARTICLE';
//Video section
$NewsLetterArr[10]['uuid']    = '*';
$NewsLetterArr[10]['limit']   = '40';
$NewsLetterArr[10]['subtype'] = 'VIDEO';
//gallery section
$NewsLetterArr[11]['uuid']    = '*';
$NewsLetterArr[11]['limit']   = '40';
$NewsLetterArr[11]['subtype'] = 'GALLERY';

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

if ( is_array( $articles ) ){
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
  'pagecontent' => $content
] );
