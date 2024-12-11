<?php
$url="https://khaleejtimes.eu.auth0.com";
$client_id="qgTRkI74FUM6zFHEao5WTNPk27z5fwfR";
$client_secret="r1xgvsH_4vQaNYy1sl7L2Z1J4DtDiBKr3_leyX9lfDsaUMjfSpZjNCnC1DKnPzIQ";
$callback_url="https://www.khaleejtimes.com/pressreader.php";
 if(@$_GET['verify_user']): 
?>
<script>
var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function() {
  if(this.readyState === 4) {
    console.log(this.responseText);
  }
});
xhr.open("GET", "https://khaleejtimes.eu.auth0.com/v2/logout");
xhr.setRequestHeader("Cookie", "");
xhr.send();
</script>
<?php
endif;
?>
<script src="https://www.khaleejtimes.com/wp-content/themes/ktd-theme/assets/js/auth0-spa-js.production.js?ver=<?=date("YmdHis")?>"></script>
<script>
auth0.createAuth0Client({
  domain: "<?=$url?>",
  clientId: "<?=$client_id?>", 
  cacheLocation:"localstorage",   
        sessionCheckExpiryDays: 5,
  authorizationParams: {
    redirect_uri:"<?=$callback_url?>?redirect="+document.referrer.split("?")[0]+"",
    scope:"openid",
    response_mode:"query",
    response_type:"code"
  }
}).then(async (auth0Client) => {
   //preventDefault();
  <?php if(!$_GET['code']): ?>
    auth0Client.loginWithRedirect();
  <?php endif; ?> 
    if (location.search.includes("state=") && 
      (location.search.includes("code=") || 
      location.search.includes("error="))) {  
    await auth0Client.handleRedirectCallback();
  }
    const isAuthenticated = await auth0Client.isAuthenticated();
    const userProfile = await auth0Client.getUser();  
    if (isAuthenticated) {    
    str = JSON.stringify(userProfile);  
    let json_obj = userProfile;
    let user_names = '';
    let real_name='';
        if (json_obj.sub.search("apple") > -1) {
          let apple_name = json_obj.name.split(' ');      
          user_names = '{"given_name": "' + sname_real + '",';
          user_names += '"family_name": "' + apple_name[1] + '",';
          real_name=apple_name[0];
          }
          else {       
          user_names = '{"given_name": "' + json_obj.given_name + '",';
          user_names += '"family_name": "' + json_obj.family_name + '",';
         real_name=json_obj.given_name;
         }
        user_names += '"user_email": "' + json_obj.email + '",';
        user_names += '"name": "' + json_obj.name + '"}';
         document.cookie = "auth0_spajs_press=" + user_names + ";domain=khaleejtimes.com;secure; path=/; max-age=" + 45 * 24 * 60 * 60;
        document.cookie = "auth0_sub_press=" + userProfile.sub + ";domain=khaleejtimes.com;secure;path=/; max-age=" + 45 * 24 * 60 * 60;
    <?php
    if(isset($_GET['redirect']))
        {
        $referrer=$_GET['redirect'];
        }
        else
        {
        $referrer="https://epaper.khaleejtimes.com";
        }
    ?>    
    location.href="<?=$referrer?>?token="+userProfile.sub;

}
});
</script>