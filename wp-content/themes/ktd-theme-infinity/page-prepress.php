<?php
$url="https://khaleejtimes-dev.eu.auth0.com";
$client_id="Og9eZ12aZ5kjqIdo8LFUrmzcqilVqKeE";
$client_secret="igzDpibDXvjc_kCFWH41V54fFkakRlotYFxS8juOZ72CbV9jgNK11gZjPS8vo-Of";
$callback_url="https://navstage.khaleejtimes.com/prepress";
session_start();
 if(@$_GET['verify_user']): 
  if(@$_GET["redirect_url"]):
    
    $_SESSION["REDIRECT_URL_TO"]=$_GET["redirect_url"];
  else:
     $_SESSION["REDIRECT_URL_TO"]="https://khaleejtimes.pressreader.com/khaleej-times";
  endif;
  ?>
<script src="https://api.khaleejtimes.com/json/auth0-spa-js.production.js?version=<?=date("YmdHis")?>"></script>
<script>
auth0.createAuth0Client({
  domain: "<?=$url?>",
  clientId: "<?=$client_id?>",   
  authorizationParams: {
    redirect_uri:"<?=$callback_url?>",
    scope:"openid",
    response_mode:"query",
    response_type:"code"
  }
}).then(async (auth0Client) => {    
    auth0Client.loginWithRedirect();
  });
</script>
<?php
endif;
if(@$_GET['code']):
   echo $_SESSION["REDIRECT_URL_TO"];
   exit;
$code=$_GET['code'];
  // $referrer=$_GET['referrer'];
  $curl = curl_init();
curl_setopt_array($curl, [
  CURLOPT_URL => "$url/oauth/token",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
CURLOPT_POSTFIELDS => "grant_type=authorization_code&client_id=$client_id&client_secret=$client_secret&code=$code&redirect_uri=$callback_url",
  CURLOPT_HTTPHEADER => [
    "content-type: application/x-www-form-urlencoded"
  ],
]);
$response = curl_exec($curl);
$response=json_decode($response,TRUE);
$err = curl_error($curl);
curl_close($curl);
$token= $response['access_token'];
echo $token;
if ($err) {
  echo "cURL Error #:" . $err;
} 
header("Location:".$_SESSION["REDIRECT_URL_TO"]."?token=$token");
endif;