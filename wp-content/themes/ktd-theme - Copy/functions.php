<?php

use KTDTheme\Startup;
use Infomaker\Everyware\Base\ProjectStartup;
use Everyware\Plugin\SettingsParameters\SettingsParameter;

ProjectStartup::registerThemeStartup(new Startup());

define( 'ContentSubType_Query_Param', '(CustomerContentSubType: "ARTICLE" OR CustomerContentSubType: "SPONSOREDCONTENT" OR CustomerContentSubType: "GALLERY" OR CustomerContentSubType: "VIDEO" OR CustomerContentSubType: "PODCAST")' );

add_action('save_post_article', static function (int $post_ID, WP_Post $post, bool $update) {
    if ( ! $update) {
        $categories = (array)$post->post_category;
        if (empty($categories) || $categories[0] === 1) {
            error_log(new Exception(sprintf('Article with ID:  %s was created without a category, url: %s'.PHP_EOL, $post_ID, $_SERVER['REQUEST_URI'])));
        }
    }
}, 10, 3);




add_action( 'wp_ajax_google_one', 'google_one' );
add_action( 'wp_ajax_nopriv_google_one', 'google_one' );

add_action( 'wp_ajax_auth0_token', 'auth0_token' );
add_action( 'wp_ajax_nopriv_auth0_token', 'auth0_token' );

add_action( 'wp_ajax_check_user', 'check_user' );
add_action( 'wp_ajax_nopriv_check_user', 'check_user' );

add_action( 'wp_ajax_check_barcode', 'check_barcode' );
add_action( 'wp_ajax_nopriv_check_barcode', 'check_barcode' );


add_action( 'wp_ajax_get_profile_c', 'get_profile_c' );
add_action( 'wp_ajax_nopriv_get_profile_c', 'get_profile_c' );

add_action( 'wp_ajax_u_tracking', 'u_tracking' );
add_action( 'wp_ajax_nopriv_u_tracking', 'u_tracking' );


function auth0_token()
{

$client_id="qnNd8uSGIsFpPZKWBrDpSasokAlRtiTA";
$domain="https://khaleejtimes.eu.auth0.com";
$ClientSecret="9f1Ymb5Zz-K0e1zi9FmNVCV96fZcYyPrrONBnUACWppzuvhHH4pFHeNHbFWs4BY5";
$ntype=@$_POST['ntype'];
$userid=@$_POST['userid'];
$returntype=@$_POST['returntype'];
if($ntype=='update'):
$output='{"app_metadata":'.json_encode($returntype).'}';
else:
$output =$returntype;	
endif;
	$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $domain."/oauth/token",			
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",			
			 CURLOPT_POSTFIELDS => "{\"client_id\":\"$client_id\",\"client_secret\":\"$ClientSecret\",\"audience\":\"$domain/api/v2/\",\"grant_type\":\"client_credentials\"}",
			  CURLOPT_HTTPHEADER => array(
				"content-type: application/json"
			  ),
			));
			$responses = curl_exec($curl);
			curl_close($curl);
			$newob=json_decode($responses, true);
			//echo $newob['access_token'];
			//echo  json_encode($newob['access_token']);
			$return_data=$ntype($output,$newob['access_token'],$userid);
			if($ntype=='check_user')
			{

				$return_data= $return_data['app_metadata'][$returntype];
			}
		if($ntype=='update' && $userid):
		$userinfo=check_user("empty",$newob['access_token'],$userid);
		$barcode=get_barcode($userinfo['email']);
		$name=explode(" ",$userinfo['name']);
		$check=update_barcode($barcode['id'],$userinfo['email'],$name[0]);
		//if($check['is_generated']):			
		$output=array("html"=>'<div class="popup-voucher-libin">
        <div class="popup-voucher-cnt2024-libin">
            <div class="coupon-bg">
                <div class="content-text">               
			<p> <img src="https://static.khaleejtimes.com/wp-content/uploads/sites/2/2024/02/15161744/pincode.png" class="pincode-logo"> </p> 
            <p> Your 50 AED  Gift Voucher<br /></p>                 
          <!-- <a href="#" onclick="closethis()" class="popup-voucher-close-libin"> X </a>   -->
            <button type="button" onclick="closethis()" class="close clos2 pfbuttons popup-voucher-close-libin" data-dismiss="modal2" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>      
            <h4>
            <span class="couponcode" id="couponcode">'.strtoupper($barcode['barcode']).' <div class="coupon-libin-copy-svg" onclick="CopyToClipboard()">  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M384 336H192c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16l140.1 0L400 115.9V320c0 8.8-7.2 16-16 16zM192 384H384c35.3 0 64-28.7 64-64V115.9c0-12.7-5.1-24.9-14.1-33.9L366.1 14.1c-9-9-21.2-14.1-33.9-14.1H192c-35.3 0-64 28.7-64 64V320c0 35.3 28.7 64 64 64zM64 128c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H256c35.3 0 64-28.7 64-64V416H272v32c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192c0-8.8 7.2-16 16-16H96V128H64z"/></svg></div> </span>
           
         <!--  <p>
            <a href="#"  onclick="CopyToClipboard(&#39;couponcode&#39;)" class="btn btn-outline-primary copy">Copy</a> 
            </p> -->
           <a href="#" onclick="tc()" class="terms-bottom">Terms & Conditions</a>
			<!-- <img alt="testing" src="/ barcode2.php?text='.strtoupper($barcode['barcode']).'&print=true&size=100&sizefactor=2&codetype=code128a" style="width:75%"/> -->
             </h4> </div>
             </div>        
        </div>
    </div>',"is_generated"=>$check['is_generated']);
			//else:
		//$output=array("html"=>'Some error Occurred, please try again.');
			//endif;
	else:
			$output=$return_data;	
		endif;			
		echo json_encode($output);
	wp_die(); 
}



function update_barcode($bid="",$email="",$user_name="")
{
$url="https://competitions.khaleejtimes.com/home/update_barcode/UTM@2050!/$bid/$email/$user_name/";
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url);
$result=curl_exec($ch);
curl_close($ch);
//var_dump(json_decode($result, true));
$arrays = json_decode($result,TRUE);
return $arrays;

}


function u_tracking()
{
$user_id=@$_POST['user_id'];
$action=@$_POST['mode'];
$url="https://competitions.khaleejtimes.com/home/u_tracking/$user_id/$action/";
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url);
$result=curl_exec($ch);
curl_close($ch);
//var_dump(json_decode($result, true));
$arrays = json_decode($result,TRUE);
return $arrays;
}


function get_barcode($useremail="")
{

$url="https://competitions.khaleejtimes.com/home/get_barcode/UTM@2050!/$useremail";
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url);
$result=curl_exec($ch);
curl_close($ch);
//var_dump(json_decode($result, true));
$arrays = json_decode($result,TRUE);
return $arrays;
}

function check_barcode()
{
$url="https://competitions.khaleejtimes.com/home/get_barcode/UTM@2050!/";
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url);
$result=curl_exec($ch);
curl_close($ch);
//var_dump(json_decode($result, true));
$arrays = json_decode($result,TRUE);
echo json_encode($arrays);
wp_die();
}

function get_profile_c()
{
$url="https://competitions.khaleejtimes.com/home/get_profile/";
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url);
$result=curl_exec($ch);
curl_close($ch);
//var_dump(json_decode($result, true));
echo $result;
wp_die();
}




function check_user($type="",$token="",$userid="")
{
$domain="https://khaleejtimes.eu.auth0.com";
	
		$curl = curl_init();
			curl_setopt_array($curl, array(
			// CURLOPT_URL => "https://khaleejtimes-dev.eu.auth0.com/api/v2/users/$user_auth0",
			CURLOPT_URL => "$domain/api/v2/users/$userid",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array("authorization: Bearer ".$token),
			));
			$response = curl_exec($curl);
			curl_close($curl);
			$newob2=json_decode($response, true);
			return $newob2;
}
	
function sync_user($user_data="",$token="")	
{
$domain="https://khaleejtimes.eu.auth0.com";
	$curl = curl_init();
		$responses = curl_exec($curl);
			$newob=json_decode($responses, true);
			curl_setopt_array($curl, array(
			 CURLOPT_URL => "$domain/api/v2/users",
			//	CURLOPT_URL => "https://khaleejtimes.eu.auth0.com/api/v2/users/$user_auth0",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,			
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $user_data,				
			 // CURLOPT_HTTPHEADER => array("authorization: Bearer ".$newob['access_token']),
			 CURLOPT_HTTPHEADER => array("authorization: Bearer ".$token,'Content-Type:  application/json'),
			));
			$response = curl_exec($curl);
			curl_close($curl);
}

function update($user_data="",$token="",$userid)
{
$domain="https://khaleejtimes.eu.auth0.com";
$curl = curl_init();
		$responses = curl_exec($curl);
			$newob=json_decode($responses, true);
			curl_setopt_array($curl, array(
			 CURLOPT_URL => "$domain/api/v2/users/$userid",
			//	CURLOPT_URL => "https://khaleejtimes.eu.auth0.com/api/v2/users/$user_auth0",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,			
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "PATCH",
			CURLOPT_POSTFIELDS => $user_data,				
			 // CURLOPT_HTTPHEADER => array("authorization: Bearer ".$newob['access_token']),
			 CURLOPT_HTTPHEADER => array("authorization: Bearer ".$token,'Content-Type:  application/json'),
			));
			$response = curl_exec($curl);
			curl_close($curl);
			$newob2=json_decode($response, true);
	return $newob2;

}


function google_one()	
{
	$user_auth0=$_POST["sdata"];	
	$user_data=array();
	$user_data['email']=$user_auth0['email'];
	$user_data['email_verified']=json_decode($user_auth0['email_verified']);
	$user_data['family_name']=$user_auth0['family_name'];
	$user_data['given_name']=$user_auth0['given_name'];	
	$user_data['connection']="Username-Password-Authentication";
	$user_data['user_metadata']["type"]="Google_One_Tap";	
	$user_data['user_metadata']["last_login"]=date("Y-m-d H:i:s");	
	$user_data['user_metadata']["login_count"]=1;
	//$user_data['last_login']=date("Y-m-d H:i:s");
	$user_data['password']=base64_encode(random_bytes(12));
	$user_data['name']=$user_auth0['name'];
	if($user_auth0['nickname']):
		$user_data['nickname']=$user_auth0['nickname'];
	endif;
	$user_data['picture']=$user_auth0['picture'];
	$user_data['user_id']=$user_auth0['sub'];
	$user_data2=json_encode($user_data);
	$token=auth0_token();
	$check_user=check_user($token,$user_auth0['email']);
	/*echo "<pre>";
	print_r($check_user);
	exit;*/
		if(!$check_user[0]['user_id'])
		{
			sync_user($user_data2,$token);			
			//login_user($token,"oaDS6HP4AbcidobSOUg7nVsQTh3Dm0Mu",$user_data['email']);			
		}	
	else
	{		
		$user_data_up=[];		
		$user_data_up['user_metadata']["last_login"]=date("Y-m-d H:i:s");	
		$user_data_up['user_metadata']["login_count"]=$check_user[0]['user_metadata']['login_count']+1;			
		$checklogin= update(json_encode($user_data_up),$token,$check_user[0]['user_id']);
		//print_r($checklogin);
	}
}


/*
  20230829 MY : merged from storytelling-v2 branch
 */
function enqueue_storytelling_styles() {
    wp_enqueue_style( 'storytelling', get_stylesheet_directory_uri().'/assets/css/storytelling.css', '', '202308161431', '');
    wp_enqueue_script( 'storytelling', get_stylesheet_directory_uri().'/assets/js/storytelling.js', '', '202308161451');
}
add_action( 'wp_enqueue_scripts', 'enqueue_storytelling_styles' );
remove_action('rest_api_init','create_initial_rest_routes',99);