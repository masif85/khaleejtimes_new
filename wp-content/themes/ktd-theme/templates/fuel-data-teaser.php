<?php
/**
 * Article Name:homepage fuel data teaser 
 */
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;
use Everyware\Everyboard\board\board_custom_post_type;
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://xlibris.public.prod.oc.ktd.infomaker.io:8443/opencontent/objects/61e79fe2-cd98-48a1-9ff7-a63983a0acd0',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array( 'Authorization: Basic a3RkOnljdnl4dmVReVI3MnhhRU5LS0BRcUJjQg=='), 
));
$response = curl_exec($curl);
$xml = simplexml_load_string($response);
$json = json_encode($xml);
$array = json_decode($json,TRUE);
curl_close($curl);
$fuel_data=$array['contentSet']['inlineXML']['idf']['group']['object']['data']['tbody']['tr'];
$table="";
foreach($fuel_data as $fdata)
{
	if($fdata['td'][0])
	{
		$tbl['thead'][]="<th><h4><a href='#''>".$fdata['td'][0]."</a></h4></th>";
	}

	if($fdata['td'][1])
	{
		$tbl['tbody'][]="<td>".$fdata['td'][1]."</td>";
	}	
}
$table.="

<div class='fuel-price-widget'><h3><a href='https://www.khaleejtimes.com/gold-forex'><img src='images/icons/fuel.svg' alt='KTFuel icon' width='20' height='20'>Fuel Price</a></h3><table class='fuel-price'><tr>".implode("",$tbl['thead'])."</tr><tr>".implode("",$tbl['tbody'])."</tr></table></div>";
echo $table;
