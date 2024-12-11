<?php
/**
 * Article Name:Fuel chart Teaser 
 */
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;
use Everyware\Everyboard\board\board_custom_post_type;
$settings="";
$url="https://api.khaleejtimes.com/home/get_fuel_chart";
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url);
$result=curl_exec($ch);
curl_close($ch);
$arrays = json_decode($result,TRUE);
	$data=array( 'fuelchart' => $arrays,
	'limit'=>$settings);
 View::render('@base/teasers/fuel-chart-teaser.twig', $data);
?>