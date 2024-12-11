<?php
/**
 * Article Name:homepage metal rates data teaser 
 */
use Infomaker\Everyware\Twig\View;
use KTDTheme\ViewModels\Teasers\Teaser;
use Everyware\Everyboard\board\board_custom_post_type;

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://xlibris.public.prod.oc.ktd.infomaker.io:8443/opencontent/search?uuid=b79e4450-81f2-46ec-8752-6c2ff8e37890%20a2344da1-7204-4131-9f2c-1b94a6688cbd%20966d14a5-be5b-484b-958c-5cebd45042cb&Status=usable',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => false,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Basic a3RkOnljdnl4dmVReVI3MnhhRU5LS0BRcUJjQg=='
  ),
));
$response = curl_exec($curl);
$json = json_encode($response);
$array = json_decode($response,TRUE);
curl_close($curl);
$arrays=$array['hits']['hits'];
$fuel_data=[];
$fuel_datas=[];

foreach($arrays as $keys=>$mdata)
{
$fuel_data[]=simplexml_load_string($mdata['versions'][0]['properties']['BodyRaw'][0]);
}
$jsonz = json_encode($fuel_data);
$arrayz = json_decode($jsonz,TRUE);
foreach($arrayz as $key=>$gdata)
{
  $fuel_datas[]=$gdata['group']['object']['data']['tbody'];
  $fuel_datas[$key]['headline']=$gdata['group']['element'][0];
}
$table="<div class='gold-price-widget'>
                    <h3><a href='https://www.khaleejtimes.com/gold-forex'><img src='images/icons/gold-bar.svg' alt='KT Gold icon' width='27' height='27'>Gold &amp; Forex </a></h3><table class='gold-price'><tr>";
$tdm="";
$itr=0;
foreach($fuel_datas as $key=>$fdata)
{
  if($fuel_datas[$key]['headline']=='UAE DRAFT RATES'): $tdm="<th><h4><a href='#'> ".$fuel_datas[$key]['headline']."</a></h4></th>"; $firsttd="INR"; $secondtd="PKR"; 
  endif;
  if($fuel_datas[$key]['headline']=='UAE GOLD RATE (AED)'): $tdm="<th><h4><a href='#'>".$fuel_datas[$key]['headline']."</a></h4></th>"; $firsttd="OUNCE (AED)"; $secondtd="24K (AED)";
  endif;
  if($fuel_datas[$key]['headline']=='SILVER RATE (AED)'): $tdm="<th><h4><a href='#'>".$fuel_datas[$key]['headline']."</a></h4></th>"; $firsttd="KILO (AED)"; $secondtd="KILO (USD)";
  endif;
  $tdata=array_slice($fdata['tr'],0,2);
  $table.= $tdm."<td>".$firsttd."<br />".$tdata[0]['td'][1]."</td>";  
  $table.="<td>".$secondtd."<br />".$tdata[1]['td'][1]."</td>";
  $table.="</tr>";
}
$table.="</tr></table>    </div>
                </div>";
echo $table;
