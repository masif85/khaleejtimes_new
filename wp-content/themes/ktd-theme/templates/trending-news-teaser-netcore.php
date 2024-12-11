<?php
/**
 * Article Name: Trending News Teaser Netcore
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


if(!isset($_GET['skip']))
{
  $set_start=3;
  $set_start2=4; 
  $wdms='$lte';
}
else
{
$set_start=$_GET['skip']+3;  
$set_start2=$_GET['skip']+4; 
$wdms='$gte'; 
}

/*
if(file_exists("sessions/".$_COOKIE['PHPSESSID'].'.txt'))
{
$myfile = fopen("sessions/".$_COOKIE['PHPSESSID'].'.txt', "r");
$set= fread($myfile,filesize("sessions/".$_COOKIE['PHPSESSID'].'.txt'));
$set=explode(",",$set);
$set= array_filter($set);
$total=count($set);
if($total>200)
{
  fopen("sessions/".$_COOKIE['PHPSESSID'].'.txt', 'w');
//$myfile = file_put_contents("sessions/".$_COOKIE['PHPSESSID'].'.txt',"", FILE_APPEND | LOCK_EX);
fclose($myfile);
unlink("sessions/".$_COOKIE['PHPSESSID'].'.txt');
}
$set='"'.implode('","', $set).'"';
}
*/

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
"rec_type": "trending",
"no_cache": true,
"related_action_as_view": true,
"related_action_type": "view",
"transaction_window": "24",
"query": {
  "userid": "",
  "boxx_token_id": "17639b83-daa6-49b1-ad35-9fdef09f4434",
  "item_filters": {"n_days_old" : {"'.$wdms.'": '.$set_start.'}, 
  "category" : "Business"},
  "related_products": [],
  "exclude": [],
  "num": 5,
  "offset": 10,
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
$result['business']       = $response_arr['result'];
/*$idz2=array_column( $response_arr['result'], 'id');
$idz2=implode(",",$idz2);
file_put_contents("sessions/".$_COOKIE['PHPSESSID'].'.txt',$idz2."," , FILE_APPEND | LOCK_EX);
*/
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
"rec_type": "trending",
"no_cache": true,
"related_action_as_view": true,
"related_action_type": "view",
"transaction_window": "24",
"query": {
  "userid": "",
  "boxx_token_id": "17639b83-daa6-49b1-ad35-9fdef09f4434",
  "item_filters": {"n_days_old" : {"$lte": '.$set_start2.'}
  },
  "related_products": [],
  "exclude": [],
  "num": 5,
  "offset": 25,
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
$result['entire']       = $response_arr['result'];

/*$idz=array_column( $response_arr['result'], 'id');
if(count($idz)==0)
{
  fopen("sessions/".$_COOKIE['PHPSESSID'].'.txt', 'w');
 // file_put_contents("sessions/".$_COOKIE['PHPSESSID'].'.txt'," ", FILE_APPEND | LOCK_EX);
}
else
{
$idz=implode(",",$idz);
file_put_contents("sessions/".$_COOKIE['PHPSESSID'].'.txt',$idz."," , FILE_APPEND | LOCK_EX);
}
*/
View::render('@base/teasers/trending-news-teaser-netcore.twig', $result);

