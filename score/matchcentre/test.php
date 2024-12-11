<?php


echo date("Hi");
exit(0);
$handle = curl_init();
 
$url = "https://rest.entitysport.com/v2/competitions/116699/matches?token=c41abd3e7e90c1e8da0fd0d3e68f3760&type=mixed&per_page=1";
 
// Set the url
curl_setopt($handle, CURLOPT_URL, $url);
// Set the result output to be a string.
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
 
$output = curl_exec($handle);
 
curl_close($handle);
 
$output  = json_decode($output,true);

		if(isset($output['response']['items'][0]['match_id']))
			{
				$match_id = $output['response']['items'][0]['match_id'];
				
					file_put_contents("matchid.txt",$match_id);
			}



?>