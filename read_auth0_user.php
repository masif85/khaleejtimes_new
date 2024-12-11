<?php
session_start();
		//print_r($_POST);
		$user_auth0=$_GET["user_id"];
		if($user_auth0!=''):
		$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://khaleejtimes-dev.eu.auth0.com/oauth/token",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => "{\"client_id\":\"oaDS6HP4AbcidobSOUg7nVsQTh3Dm0Mu\",\"client_secret\":\"eerwha1AlQAP_VNwnQSeiktNXDOA9o023sCxjLC0LoP0514N6SSNAyZ5K7gp_s4R\",\"audience\":\"https://khaleejtimes-dev.eu.auth0.com/api/v2/\",\"grant_type\":\"client_credentials\"}",
			  CURLOPT_HTTPHEADER => array(
				"content-type: application/json"
			  ),
			));
			$responses = curl_exec($curl);
			$newob=json_decode($responses, true);
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://khaleejtimes-dev.eu.auth0.com/api/v2/users/$user_auth0",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array("authorization: Bearer ".$newob['access_token']),
			));
			$response = curl_exec($curl);
			curl_close($curl);
			$newob2=json_decode($response, true);	
				setcookie("auth0_user_id", $newob2['user_id']);		
				setcookie("auth0_email", $newob2['email']);
				setcookie("auth0_name", $newob2['name']);
				setcookie("auth0_nickname",$newob2['nickname']);
				setcookie("auth0_last_ip", $newob2['last_ip']);		
				setcookie("auth0_family_name", $newob2['family_name']); 
				setcookie("auth0_given_name", $newob2['given_name']);
				setcookie("auth0_picture",$newob2['picture']);
			if(@$newob2['user_metadata']['newsletter']){
				setcookie("auth0_newsletter",$newob2['user_metadata']['newsletter']); 
			}		
			echo json_encode($newob2);
			//$newob2['given_name'];
			else:
			$data=array("given_name"=>"Reader");
			echo json_encode($data);
			endif;
		

?>