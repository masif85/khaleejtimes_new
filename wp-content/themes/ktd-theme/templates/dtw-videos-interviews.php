<?php
/**
 * Article Name: DTW Videos Interviews Teaser 
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
/*$url="https://ktshows.khaleejtimes.com/home/gen_videos/Interviews";
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$result=curl_exec($ch);
curl_close($ch);*/
//$result= file_get_contents("https://ktshows.khaleejtimes.com/home/gen_videos/Interviews/");
//$arrays = json_decode($result,TRUE);
$arrays= "";
	if (($result = @file_get_contents("https://ktshows.khaleejtimes.com/home/gen_videos/Interviews/")) !== false) {     
	     $arrays = json_decode($result,TRUE);
	} else {    
		$arrays= "";
	}
	$data=array( 'video' => $arrays,
	'limit'=>$settings);
 View::render('@base/teasers/dtw-videos-teaser.twig', $data);