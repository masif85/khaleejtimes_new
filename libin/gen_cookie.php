<?php
$url="https://competitions.khaleejtimes.com/home/get_barcode/UTM@2050!";
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url);
$result=curl_exec($ch);
curl_close($ch);
//var_dump(json_decode($result, true));
$arrays = json_decode($result,TRUE);
echo $arrays['barcode'];

?>