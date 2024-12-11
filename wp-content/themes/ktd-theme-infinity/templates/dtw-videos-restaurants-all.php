<?php
/**
 * Article Name:DTW Videos Restaurants All Teaser 
 */

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
$url="https://ktshows.khaleejtimes.com/home/gen_videos/Restaurants/all/";
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url);
$result=curl_exec($ch);
curl_close($ch);
//var_dump(json_decode($result, true));
$arrays = json_decode($result,TRUE);
	$data=array( 'video' => $arrays,
	'limit'=>$settings);
 View::render('@base/teasers/dtw-videos-teaser-all.twig', $data);