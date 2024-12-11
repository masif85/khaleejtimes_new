<?php
/**
 * Article Name: Recommended news teaser Mobile netcore
 */

use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;
use Everyware\Everyboard\board\board_custom_post_type;
use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\ArticlePage;
use Infomaker\Everyware\Base\Models\Post;
$excludes="";
$set="";
$boxTokenId = '';
if( isset( $_COOKIE['boxx_token_id'] ) ){
  $boxTokenId = $_COOKIE['boxx_token_id'];
}

if( isset( $_COOKIE['boxx_token_id'] ) ){
  $boxTokenId = $_COOKIE['boxx_token_id'];
}
if(!isset($_GET['skip']))
{
  $set_start=3;
  $wdms='$lte';
}
else
{
$set_start=$_GET['skip']+3;  
$wdms='$gte'; 
}
$data=explode("/",$_SERVER['REQUEST_URI']); 
$section= UCFIRST(str_replace('-', ' ', $data[1]));
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
                  "boxx_token_id": "17639b83-daa6-49b1-ad35-9fdef09f4434",
                  "item_filters": {"n_days_old" : {"$lte": '.$set_start.'}},
                  "related_products": [],
                  "exclude": [],
                  "num": 5,
                  "offset": 20,
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
$result['items'] = $response_arr['result'];
//"item_filters": {"n_days_old" : {"$lte": '.$set_start.'},"category" : "'.$section.'"},
View::render('@base/teasers/recommended-news-teaser-mobile-netcore.twig', $result);