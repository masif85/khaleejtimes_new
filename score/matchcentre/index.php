<?php ob_start(); session_start();

	$gamecode = isset($_GET['gamecode'])?$_GET['gamecode']:0;
	
	
	//$base_http_url	=	"https://cricket.khaleejtimes.com/ipl-2021/";
	
	$field		=	isset($_GET['field'])?$_GET['field']:0;
			
		if(empty($field))
			{
				
			// for gettin latest match id here 
				if(date("Hi")>1300 && date("Hi")<2000 && !isset($_SESSION['match_id']))
				// if(1)
					{
						$handle = curl_init();
 
							$url = "https://rest.entitysport.com/v2/competitions/116699/matches?token=c41abd3e7e90c1e8da0fd0d3e68f3760&type=mixed&per_page=1";   // IPL 2020
							
							$url = "https://rest.entitysport.com/v2/matches?token=c41abd3e7e90c1e8da0fd0d3e68f3760&type=mixed&per_page=1";  // overall all 
							 
							$url = "https://rest.entitysport.com/v2/competitions/118273/matches?token=c41abd3e7e90c1e8da0fd0d3e68f3760&type=mixed&per_page=1"; // IPL 2021
							
							// Set the url
							curl_setopt($handle, CURLOPT_URL, $url);
							// Set the result output to be a string.
							curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
							 
							$output = curl_exec($handle);
							 
							curl_close($handle);
							 
							$output  = json_decode($output,true);
							
									if(isset($output['response']['items'][0]['match_id']))
										{
											$_SESSION['match_id'] = $match_id = $output['response']['items'][0]['match_id'];
											
												file_put_contents("matchid.txt",$match_id);
										}
					} 
			
			// for gettin latest match id here 	
				
						$match_id = file_get_contents("matchid.txt");
						$matchcenterurl	=	"/score/matchcentre/?field=entity_cricket&id=$match_id&widget=match_center";
					header("Location: $matchcenterurl	"); exit(0);
					//header("Location: https://cricket.khaleejtimes.com/matchcentre/?field=live");  exit(0);
			}
				define("THECACHEVERSION",2.110);
				
	// echo date("Hi")."#";						
?>
