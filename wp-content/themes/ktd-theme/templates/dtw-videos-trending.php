<?php
/**
 * Article Name: DTW Videos Trending Teaser 
 */
//require_once dirname(__FILE__) . '/board_settings.php';
//require_once "/var/www/wp-content/plugins/everyboard/board/board_article_helper.php";
//use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;
use Everyware\Everyboard\board\board_custom_post_type;
//use Everyware\Everyboard\OcArticleProvider;
//$settings=EveryBoard_CustomPostType::get_list_id();
$settings="";//Teaser::getMainImages($article);
//$settings=new board_article_helper(EveryBoard_Article_Helper::prefetch_board_articles());
//$setting=$this->EveryBoard_Article_Helper->prefetch_board_articles();	
$limit=10;//$_POST['limit'];
//$xml = simplexml_load_file("https://api.performfeeds.com/vod/lnh8vxbvvys15zekrmmhwd9f/?_rt=b&vo=os&_fmt=xml&_pgSz=12&_ord=pt&_ordSrt=desc");
//$json = json_encode($xml);
/*$url="https://ktshows.khaleejtimes.com/home/trending/";
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url);
$result=curl_exec($ch);
curl_close($ch);*/
 $curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://ktshows.khaleejtimes.com/vr/app/trending/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_FOLLOWLOCATION => false,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'x-api-key: 56456498212',
    'Authorization: Basic dnJfYWRtaW46dnJfa3RAMjA1MA=='
  ),
));
$result = curl_exec($curl);
curl_close($curl);
$arrays = json_decode($result,TRUE);
	$data=array( 'video' => $arrays,
	'limit'=>$settings);
 View::render('@base/teasers/dtw-videos-trending.twig', $data);