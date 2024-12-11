<?php
/**
 * Article Name: Topnews left sidebar netcore
 */

use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;
use Everyware\Everyboard\board\board_custom_post_type;
use USKit\Base\Parts\NewsMLArticle;
use USKit\Base\ViewModels\ArticlePage;
use Infomaker\Everyware\Base\Models\Post;
if(strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'xmlhttprequest')
{    
    // We can assume that request was made with AJAX 
   // unset($_SESSION['excludes']);  
}
$excludes="";
$set="";

$boxTokenId = '';
if( isset( $_COOKIE['boxx_token_id'] ) ){
  $boxTokenId = $_COOKIE['boxx_token_id'];
}
if(!isset($_GET['skip']))
{
  $set_start=2;
   $wdms='$lte';
}
else
{
$set_start=$_GET['skip']+2;  
$wdms='$gte'; 
}
/*
if(file_exists("sessions/".$_COOKIE['PHPSESSID'].'.txt'))
{
$myfile = fopen("sessions/".$_COOKIE['PHPSESSID'].'.txt', "r");
$set= fread($myfile,filesize("sessions/".$_COOKIE['PHPSESSID'].'.txt'));
$set=explode(",",$set);
$total=count($set);
$set= array_filter($set);
if($total>200)
{
  fopen("sessions/".$_COOKIE['PHPSESSID'].'.txt', 'w');
//file_put_contents("sessions/".$_COOKIE['PHPSESSID'].'.txt',"", FILE_APPEND | LOCK_EX);
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
  "item_filters": {"n_days_old" : {"'.$wdms.'": '.$set_start.'}},
  "related_products": [],
  "exclude": [],
  "num": 3,
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
$result['items']       = $response_arr['result'];
/*$idz=array_column( $response_arr['result'], 'id');
if(!$idz)
{
  fopen("sessions/".$_COOKIE['PHPSESSID'].'.txt', 'w');
}
else
{
$idz=implode(",",$idz);
file_put_contents("sessions/".$_COOKIE['PHPSESSID'].'.txt',$idz."," , FILE_APPEND | LOCK_EX);
}
*/
View::render('@base/teasers/topnews-left-sidebar-netcore.twig', $result);