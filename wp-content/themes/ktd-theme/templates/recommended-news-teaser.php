<?php
/**
 * Article Name:Recommended News Teaser
 */

use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;
use Everyware\Everyboard\board\board_custom_post_type;
use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\ArticlePage;
use Infomaker\Everyware\Base\Models\Post;

$settings="";	
$limit=10;

$boxTokenId = '';
if( isset( $_COOKIE['boxx_token_id'] ) ){
  $boxTokenId = $_COOKIE['boxx_token_id'];
}

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://loki.boxx.ai/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "client_id": "x9vk",
    "access_token": "478b112d-8bd1-49ad-ad97-1f44b023705c",
    "channel_id": "DyMq",
    "is_internal": false,
    "is_boxx_internal": false,
    "rec_type": "boxx",
    "no_cache": true,
    "related_action_as_view": true,
    "related_action_type": "view",
    "transaction_window": "24",
    "query": {
        "userid": "",
        "boxx_token_id": "'.$boxTokenId.'",
        "item_filters": {},
        "related_products": [],
        "exclude": [],
        "num": 4,
        "get_product_properties": true,
        "get_product_aliases": false
    }
  }',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
  ),
));

$response = curl_exec($curl);
curl_close($curl);

$response_arr = json_decode( $response, true );
$result       = $response_arr['result'];

$data = array();
$username = 'ktd';
$password = 'ycvyxveQyR72xaENKK@QqBcB';

foreach( $result as $key => $res) {
    $id           = $res['id']; 

    $uuid         = $res['properties']['guid']['#text'];

    $imageParams  = '&source=false&q=75&crop_w=0.76&crop_h=0.64981&width=105&height=59&x=0.2075&y=0.12734%20105w';
    $image        = $res['properties']['image'];
    $image        = str_replace( 'function=original', 'function=cropresize',$image );
    $image        = $image . $imageParams;


    // Call OneContent API
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://xlibris.public.prod.oc.ktd.infomaker.io:8443/opencontent/search?uuid='.$uuid,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => false,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_USERPWD => $username . ":" . $password,
    ));
    
    $responseOneContent = curl_exec($curl);
    curl_close($curl);
  
    $responseOneContent = json_decode( $responseOneContent, true );

    if( isset( $responseOneContent['hits']['hits'][0]['versions'] ) ){
        $articleData = array_shift( $responseOneContent['hits']['hits'][0]['versions'] );
        $articleData = $articleData['properties'];

        $contentSubType = isset( $articleData['contentsubtype'] ) ? $articleData['contentsubtype'] : "";
        // $image          = array_shift( $articleData['TeaserImageUuids'] );

        $data['items'][] = array(
          'uuid'                => array_shift( $articleData['uuid'] ),
          // 'pubdate' => $article->getPubDate()->diffForHumans(),
          'headline'            => array_shift( $articleData['Headline'] ),
          // 'leadin'              => array_shift( $articleData['leadin'] ),
          'authors'             => $res['properties']['author'],
          'permalink'           => $res['properties']['link'],
          'sectionName'         => array_shift( $articleData['Section'] ),
          'teaserbody'          => array_shift( $articleData['TeaserBody'] ),
          'contentsubtype'      => $contentSubType,
          'conceptsectionuuids' => $articleData['ConceptSectionUuids'],
          'image'               => $image
        );
    }
}

/*echo '<pre>';
print_r( $data ); die;
*/
View::render('@base/teasers/recommended-news-teaser.twig', $data);