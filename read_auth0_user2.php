<?php
session_start();
		//print_r($_POST);
		$user_auth0=$_GET["user_id"];
		if($user_auth0):
		$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://khaleejtimes-dev.eu.auth0.com/oauth/token",
				//CURLOPT_URL => "https://khaleejtimes.eu.auth0.com/oauth/token",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
				//CURLOPT_POSTFIELDS => "{\"client_id\":\"qnNd8uSGIsFpPZKWBrDpSasokAlRtiTA\",\"client_secret\":\"9f1Ymb5Zz-K0e1zi9FmNVCV96fZcYyPrrONBnUACWppzuvhHH4pFHeNHbFWs4BY5\",\"audience\":\"https://khaleejtimes.eu.auth0.com/api/v2/\",\"grant_type\":\"client_credentials\"}",
			 CURLOPT_POSTFIELDS => "{\"client_id\":\"oaDS6HP4AbcidobSOUg7nVsQTh3Dm0Mu\",\"client_secret\":\"eerwha1AlQAP_VNwnQSeiktNXDOA9o023sCxjLC0LoP0514N6SSNAyZ5K7gp_s4R\",\"audience\":\"https://khaleejtimes-dev.eu.auth0.com/api/v2/\",\"grant_type\":\"client_credentials\"}",
			  CURLOPT_HTTPHEADER => array(
				"content-type: application/json"
			  ),
			));
			$responses = curl_exec($curl);
			$newob=json_decode($responses, true);
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://khaleejtimes-dev.eu.auth0.com/api/v2/users/$user_auth0",
				//CURLOPT_URL => "https://khaleejtimes.eu.auth0.com",
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

if($newob2['given_name']=='')
{
	if($newob2['identities'][0]['provider']=='apple')
	{
		$newob2['given_name']=$newob2['first_name']	;
	}
	else
	{
		$newob2['given_name']="Reader"	;
	}
}
				//if( $newob2['email_verified']==false)
				//{
					//verify_user($user_auth0,$newob['access_token']);
				//}

				
			if(@$newob2['user_metadata']['newsletter']){				
				$newob2['is_newsletter']=$newob2['user_metadata']['newsletter'];
			}		

			//echo json_encode($newob2);
echo $_GET['jsoncallback']."(". json_encode($newob2). ")";
			//$newob2['given_name'];
			else:
			$data=array("given_name"=>"Reader");
			//echo json_encode($data);
echo $_GET['jsoncallback']."(". json_encode($data). ")";
			endif;

/*function verify_user($userid="",$token)	
	{
	
			$curl = curl_init();	
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://khaleejtimes-dev.eu.auth0.com/api/v2/users/$userid",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "PATCH",
			CURLOPT_POSTFIELDS => "{ \"email_verified\": true}",
			  CURLOPT_HTTPHEADER => array("authorization: Bearer ".$token),
			));
			$response = curl_exec($curl);
			curl_close($curl);
	
	
	}*/

?>