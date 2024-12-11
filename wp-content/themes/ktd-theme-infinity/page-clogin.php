 <link rel="stylesheet" href="https://www.khaleejtimes.com/wp-content/themes/ktd-theme/assets/css/bootstrap/bootstrap.min.css" />
<!-- Added 1-22-2021, Fonts Stag and Lyon -->
<link rel="stylesheet" href="https://www.khaleejtimes.com/wp-content/themes/ktd-theme/assets/css/fonts.css">
<!-- Added 1-22-2021, Fonts Stag and Lyon -->
<link rel="stylesheet" href="https://www.khaleejtimes.com/wp-content/themes/ktd-theme/assets/css/style-2-18-2021.css?ver=3.0.1.8">
<link rel="stylesheet" href="https://www.khaleejtimes.com/wp-content/themes/ktd-theme/assets/css/new-home-layout.css?ver=3.0.1.3">
<link rel="stylesheet" href="https://www.khaleejtimes.com/wp-content/themes/ktd-theme/assets/css/compressed-home-layout.css?ver=1.0.0.6">
<link rel="stylesheet" href="https://www.khaleejtimes.com/wp-content/themes/ktd-theme/assets/css/style-2-18-2021-custom.css?ver=3.0.0.2">
<link rel="stylesheet" href="https://www.khaleejtimes.com/wp-content/themes/ktd-theme/assets/css/fa/all.min.css">
<script  src="https://www.khaleejtimes.com/wp-content/themes/ktd-theme/assets/js/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
<span id="profile"></span>

<div class="row search-burmenu-bar-nf">
      <div class="col-lg-12 d-flex justify-content-end">
        <div class="header-right-column " id="userhandler">
          <ul class="header-right-icons">
                <li  id="MG2login"><a class="blink-bg-sign-in" href="#">Sign In</a></li>
                <li data-mg2-action="hideloggedout" id="MG2Detail">
                    <div class="dropdown">
                        <button type="button" onclick="engageDropdownHandler()" class="dropbtn dropdown-toggle auth0_user "><span id="MG2UserName" class="auth0_user ">Hi, <span class="has-spinner"></span></span></button>
                        <div id="engageDropdown" class="dropdown-content">
                            <a href="https://www.khaleejtimes.com/mykt">My KT</a>
                            <a href="https://www.khaleejtimes.com/trading">Trading</a>
                            <a href="https://www.khaleejtimes.com/contact-us">Contact Us</a>
                            <a href="https://www.khaleejtimes.com/privacy-policy">Privacy Policy</a>
                            <a href="#" id="MG2logout">Sign Out</a>
                        </div>
                    </div>
                </li>                
          </ul>
        </div>
      </div>
    </div>
<script>
(function ($) {
    $('.has-spinner').attr("disabled", false);
    $.fn.buttonLoader = function (action) {
        var self = $(this);
        if (action == 'start') {           
            $('.has-spinner').attr("disabled", true);
            $('.has-spinner').prepend('<span class="spinner"><i class="fa fa-spinner fa-spin" style="color:white"></i></span> ');
            $('.has-spinner').addClass('active');
        }
        if (action == 'stop') {         
            $('.has-spinner').removeClass('active');
            $('.has-spinner').attr("disabled", false);
        }
    }
})(jQuery);

$.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null) {
       return null;
    }
    return decodeURI(results[1]) || 0;
}
</script>
<div id='myHiddenPage' style="display:none;"></div>
<script src="https://api.khaleejtimes.com/json/auth0-spa-js.production.js?v=dsgfdsreertrtrer43"></script>
<script>
jQuery("#MG2login").hide();
//jQuery("#userhandler").hide();
//jQuery("#userhandler").hide();
jQuery(".has-spinner").buttonLoader('start');
    auth0.createAuth0Client({
      domain: "khaleejtimes-dev.eu.auth0.com",  
      clientId: "Og9eZ12aZ5kjqIdo8LFUrmzcqilVqKeE",  
        cacheLocation:"localstorage",     
        sessionCheckExpiryDays: 5,
      authorizationParams: {
        redirect_uri:"https://site.everywarestarterkit.local/clogin?redirect="+window.location.pathname,
        scope:"openid",
        response_mode:"query",
        response_type:"code"
      }
    }).then(async (auth0Client) => {         
  const loginButton = document.getElementById("MG2login");
  loginButton.addEventListener("click", (e) => {
    e.preventDefault();
   auth0Client.loginWithRedirect();
  });
  if (location.search.includes("state=") && 
      (location.search.includes("code=") || 
      location.search.includes("error="))) {
    jQuery('#cover-spin').show(0);
    await auth0Client.handleRedirectCallback();      
   // window.history.replaceState({}, '', window.location.pathname);    
    //window.history.replaceState({}, '', getCookie('current_url'));
    //document.cookie = "current_url=;path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC";   
     window.history.pushState({}, '',decodeURIComponent($.urlParam('redirect')));
    location.reload();
  }
    const logoutButton = document.getElementById("MG2logout");
    const MG2Detail = document.getElementById("MG2Detail");

    jQuery("#MG2logout").click(function(e){
     e.preventDefault();
        auth0Client.logout();   
        document.cookie = "auth0_sub=;secure;path=/;domain=khaleejtimes.com;expires=Thu, 01 Jan 1970 00:00:00 UTC;";
        document.cookie = "auth0_spajs=;secure;path=/;domain=khaleejtimes.com; expires=Thu, 01 Jan 1970 00:00:00 UTC;"; 
        //document.cookie = "page_cancel=;secure;path=/;domain=navstage.khaleejtimes.com; expires=Thu, 01 Jan 1970 00:00:00 UTC;";  
        //document.cookie = "page_cancel=;secure;path=/;domain=khaleejtimes.com; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
       // $.ajaxSetup({ cache: false });
       // $.ajax({url:"https://khaleejtimes-dev.eu.auth0.com/v2/logout",crossDomain: true,dataType: 'jsonp',cache: false,async: false});
       // $.get({url:"https://khaleejtimes-dev.eu.auth0.com/v2/logout",crossDomain: true,dataType: 'jsonp',cache: false,async: false});


 location.href="https://khaleejtimes-dev.eu.auth0.com/v2/logout?returnTo="+window.location.href;


        jQuery("#MG2login").show();
        jQuery("#MG2Detail").hide();
        jQuery(".has-spinner").buttonLoader('stop');
    }); 

    const isAuthenticated = await auth0Client.isAuthenticated();
    const userProfile = await auth0Client.getUser();

    const profileElement = document.getElementById("profile");
    const MG2UserName = document.getElementById("MG2UserName");

  if (isAuthenticated) {
  //alert(window.history.back());
      str = JSON.stringify(userProfile); 
      
             let json_obj = userProfile;  
             
             let iscompleted = await isprofilecompleted(userProfile.sub,'contact');  
    
            if(iscompleted=='null' && getCookie("page_cancel")!==userProfile.sub && window.location.href.indexOf("complete_profile") == -1 && !getCookie("auth0_sub"))
              {             
             window.location.href="complete_profile";
              } 
             
                   let user_names = '';
                   let real_name='';
                            if (json_obj.sub.search("apple") > -1) {
                                let apple_name = json_obj.name.split(' ');
                                user_names = '{"given_name": "' + apple_name[0] + '",';
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
                            document.cookie = "auth0_spajs=" + user_names + ";secure; domain=." +
                                location.hostname.split('.').reverse()[1] + "." +
                                location.hostname.split('.').reverse()[0] + "; path=/; max-age=" + 45 * 24 * 60 * 60;
                                document.cookie = "auth0_sub=" + userProfile.sub + ";secure; domain=." +
                                location.hostname.split('.').reverse()[1] + "." +
                                location.hostname.split('.').reverse()[0] + "; path=/; max-age=" + 45 * 24 * 60 * 60;
    
   
      
    jQuery("#MG2login").hide();
    jQuery("#userhandler").show();
    jQuery(".has-spinner").buttonLoader('stop');
    jQuery(".auth0_user").css({"min-height":"34px","max-height":"34px"});
    jQuery("#MG2UserName").animate({width:jQuery("#MG2Detail").width()+10+'px'},1000).text(short_text("Hi, "+real_name,12));
          
  } else {
    alert("sdf");
    jQuery("#MG2Detail").hide();
    jQuery("#MG2UserName").hide();
    jQuery("#userhandler").show();
    jQuery("#MG2login").show();
    jQuery("#MG2Detail").hide();
    jQuery(".has-spinner").buttonLoader('stop');
  }
});     
    
    
    async function isprofilecompleted(userprofiles,returntype) {
    return new Promise((resolve, reject) => {

        
        jQuery.ajax({
           url: "/wp-admin/admin-ajax.php", 
           type: "POST",            
            async: false,
            imeout: 30000,      
            jsonp: "jsoncallback",
           data: {'action': 'auth0_token',userid:userprofiles,returntype:returntype,ntype:'check_user'}, 
           success:function (result) {  
            resolve(result);
           //window.location.href="libin/index.html";               
            },
            error: (result) => {
                reject(result);
            }
         })     
})
}
function engageDropdownHandler() {
        document.getElementById("engageDropdown").classList.toggle("show");
    }
    window.onclick = (event) => {
        if (!event.target.matches(".auth0_user")) {
            let dropdowns = document.querySelectorAll(".dropdown-content");
            let dropdownArr = Array.from(dropdowns);
            dropdownArr.map((el) => {
                if (el.classList.contains("show")) {
                    el.classList.remove("show");
                }
            });
        }
        else
        {
       // dropdown-content.classList.remove("show"); 
         
        }
};      
    
function short_text(auth0_text, count){
    return auth0_text.slice(0, count) + (auth0_text.length > count ? "..." : "");
}   
    
//20230418 MCD: get cookie by name function
//20230510 MCD: Moved getCookie function outside of engage onLoggedIn event
function getCookie(cookieName) {
    let cookie = {};
    document.cookie.split(';').forEach(function (el) {
        let [key, value] = el.split('=');
        cookie[key.trim()] = value;
    })
    return cookie[cookieName];
}


// GSI Google One tap script
let b64DecodeUnicode = str =>
  decodeURIComponent(
    Array.prototype.map.call(atob(str), c =>
      '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
    ).join(''))

let parseJwt = token =>
  JSON.parse(
    b64DecodeUnicode(
      token.split('.')[1].replace('-', '+').replace('_', '/')
    )
  )

window.isAuthenticated = false;
window.identity = {};
window.token = '';

function handleCredentialResponse(response) {
    window.token = response.credential;
    window.identity = parseJwt(response.credential);
    window.isAuthenticated = true;
    showAuthInfo();
}
function sync_user() {
/* jQuery.ajax({
   url: "/wp-admin/admin-ajax.php", 
   type: "POST", 
   dataType: "json",
   data: {'action': 'google_one',sdata:window.identity},  
   success: function (result) {     
    },
    error: function (err) {
  
    }
 }); */
        
     jQuery.ajax({
        url: "https://api.khaleejtimes.com/json/syncdata.php",
        type: "GET",
        dataType: "jsonp", jsonp: 'jsoncallback',
        data: { sdata:  window.identity},
        success: function (result) {
       
        },
        error: function (err) {
            // check the err for error details
        }
    }); 
    
    
    
}
async function showAuthInfo() {
    if (window.isAuthenticated) { 
         user_names = '{ "given_name": "' + window.identity.given_name + '",'; 
        user_names += '"family_name": "' + window.identity.family_name + '",';  
        //user_names += '"name": "' + result.name + '"}';
        user_names += '"user_email": "' + window.identity.email + '"}';     
        document.cookie = "auth0_sub=google-oauth2|" + window.identity.sub + ";secure; domain=" + location.hostname + "; path=/; max-age=" + 45*24*60*60;
         document.cookie = "auth0_spajs=" + user_names + ";secure; domain=" + location.hostname + "; path=/; max-age=" + 45*24*60*60;
        jQuery("#MG2login").css("display","none");
        //jQuery("#buttonDiv").css("display","none");
        jQuery("#MG2UserName").css("display","");
        jQuery("#MG2logout").css("display","");
        jQuery("[data-mg2-action='hideloggedout']").css("display","");  
        document.getElementById('MG2UserName').textContent = "Hi, " + short_text(`${window.identity.given_name}`,9);
         window.isAuthenticated = true;
        sync_user(); 
           let iscompleted = await isprofilecompleted(userProfile.sub,'contact');  
    
            if(iscompleted=='null' && getCookie("page_cancel")!==userProfile.sub && window.location.href.indexOf("complete_profile") == -1 && !getCookie("auth0_sub"))
              {             
             window.location.href="complete_profile";
              }  
    } 
}
if (! getCookie("auth0_sub")) {                       
window.onload = function () {
    if(! getCookie("gsi_session")){
    window.isAuthenticated = false; 
    showAuthInfo();
    google.accounts.id.initialize({
        client_id: "845685274514-8sfp3avaufrnj46cotnq1bvrbgts05ni.apps.googleusercontent.com",
        hl: 'EN',       
        //1015460415734-h8dutoq6k0vfa25osbada7egd6lrmsa1.apps.googleusercontent.com
        callback: handleCredentialResponse,
        //prompt_parent_id: 'g_id_onload',
        auto_select: false,
    }); 
    google.accounts.id.prompt((notification) => {
    //if (notification.isNotDisplayed()) return;
         //if (notification.isNotDisplayed() || notification.isSkippedMoment()) {
            if (notification.isNotDisplayed() && ! getCookie("gsi_session")) {
                document.cookie =  `g_state=;path=/;expires=Thu, 01 Jan 1970 00:00:01 GMT`;
                google.accounts.id.prompt()
            }
    
    const googlePromptFrame = document.querySelector("#credential_picker_container iframe");        
    if (googlePromptFrame) jQuery("#credential_picker_container").css({"position": "absolute", "left": "67%","top": "", "width": "0","height": "0", "z-index": "1001"});;
        }); 
   // google.accounts.id.prompt(); 
    //jQuery("#credential_picker_container").css({"position": "absolute", "left": "79%","top": "124px", "width": "0","height": "0", "z-index": "1001"});



  
    }
}

}

if(! getCookie("gsi_session"))
{
        //var g_session=new Date();
        //g_session.setTime(g_session.getTime()+(1*24*60*60*1000));
        //var gexpires = "expires="+g_session.toGMTString();
        //var valuez=getCookieValue('gsi_session'); 

        var gvalue=1;
        document.cookie = "gsi_session="+ gvalue+";path=/";     
}

</script>