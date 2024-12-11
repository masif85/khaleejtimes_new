<?php
/**
 * Article Name:today event Teaser 
 */
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;
use Everyware\Everyboard\board\board_custom_post_type;
$settings="";
$url="https://api.khaleejtimes.com/home/match_detail";
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url);
$result=curl_exec($ch);
curl_close($ch);
$arrays = json_decode($result,TRUE);
	$data=array( 'fueldata' => $arrays,
	'limit'=>$settings);
print_r($arrays);
exit;
 View::render('@base/teasers/fuel-data-teaser.twig', $data);
?>