jQuery("#MG2login").hide();
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

jQuery.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null) {
       return null;
    }
    return decodeURI(results[1]) || 0;
}

//jQuery("#userhandler").hide();
jQuery(".has-spinner").buttonLoader('start');
    auth0.createAuth0Client({
      domain: "khaleejtimes.eu.auth0.com",  
      clientId: "qgTRkI74FUM6zFHEao5WTNPk27z5fwfR",  
		cacheLocation:"localstorage",	  
        sessionCheckExpiryDays: 5,
      authorizationParams: {
        redirect_uri:"https://www.khaleejtimes.com?redirect="+window.location.pathname,
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
	let waituntilcomplete=false;
    await auth0Client.handleRedirectCallback();      
   // window.history.replaceState({}, '', window.location.pathname);	
	//window.history.replaceState({}, '', getCookie('current_url'));
	//document.cookie = "current_url=;path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC";
	var redirectz=decodeURIComponent(jQuery.urlParam('redirect'));
	var redirect=redirectz.replace(/[&\\\#,+()$~%.'":*?<>{}]/g, '');
	window.history.pushState({}, '',redirect);	
	if(waituntilcomplete!=true){
    if(redirect!="/" && redirect!=0)
    {
		location.reload();
    }
	else
	{
		jQuery('#cover-spin').hide(0);
	}
	}
  }
    const logoutButton = document.getElementById("MG2logout");
    const MG2Detail = document.getElementById("MG2Detail");

    jQuery("#MG2logout").click(function(e){
     e.preventDefault();
        auth0Client.logout();   
		document.cookie = "auth0_sub=;secure;path=/;domain=khaleejtimes.com;expires=Thu, 01 Jan 1970 00:00:00 UTC;";
		document.cookie = "auth0_spajs=;secure;path=/;domain=khaleejtimes.com; expires=Thu, 01 Jan 1970 00:00:00 UTC;";	
		document.cookie = "gsi_session=;secure;path=/;domain=khaleejtimes.com; expires=Thu, 01 Jan 1970 00:00:00 UTC;";	
		document.cookie = "auth0_sub=;secure;path=/;domain=www.khaleejtimes.com;expires=Thu, 01 Jan 1970 00:00:00 UTC;";
		document.cookie = "auth0_spajs=;secure;path=/;domain=www.khaleejtimes.com; expires=Thu, 01 Jan 1970 00:00:00 UTC;";	
		document.cookie = "gsi_session=;secure;path=/;domain=www.khaleejtimes.com; expires=Thu, 01 Jan 1970 00:00:00 UTC;";	
		//document.cookie = "page_cancel=;secure;path=/;domain=navstage.khaleejtimes.com; expires=Thu, 01 Jan 1970 00:00:00 UTC;";	
		document.cookie = "page_cancel=;secure;path=/;domain=khaleejtimes.com; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
        //jQuery.ajaxSetup({ cache: false });
		//jQuery.ajax({url:"https://khaleejtimes.eu.auth0.com/v2/logout",crossDomain: true,dataType: 'jsonp',cache: false,async: false});
		//jQuery.get({url:"https://khaleejtimes.eu.auth0.com/v2/logout",crossDomain: true,dataType: 'jsonp',cache: false,async: false});
		location.href="https://khaleejtimes.eu.auth0.com/v2/logout?client_id=qgTRkI74FUM6zFHEao5WTNPk27z5fwfR&returnTo="+window.location.origin;
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
			let iscompleted = await isprofilecompleted(userProfile.sub,'interests'); 		 
			let is_available = await checkbcode();      
			if(iscompleted=='null' && !getCookie("act_cmplt") && getCookie("page_cancel")!=userProfile.sub && window.location.href.indexOf("complete_profile") == -1 && !getCookie("auth0_sub"))
              {  
				userid_auth0=userProfile.sub;
				//window.location.href="complete_profile";
				let profile=await getprofile(userid_auth0);
				jQuery("body").append(profile);
				jQuery('#profileModal').modal('show');	
				waituntilcomplete=true;
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
							 user_names += '"user_hashemail": "' + SHA256(json_obj.email) + '",';
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
	// jQuery(".auth0_user").css({"min-height":"34px","max-height":"34px"});
	// jQuery(".dropbtn ").css({"min-width":"auto"});
	jQuery("#MG2UserName").animate().text(short_text("Hi, "+real_name,12));
    jQuery("#MG2Detail").show();
    jQuery("#MG2UserName").show();
   // jQuery("#MG2UserName").animate({width:jQuery("#MG2Detail").width()+10+'px'},1000).text(short_text("Hi, "+real_name,12));jQuery("#MG2UserName").animate({width:jQuery("#MG2Detail").width()+10+'px'},1000).text(short_text("Hi, "+real_name,12));
    jQuery("#MG2UserName ").css({"width":"auto","overflow": "hidden"});      
  }   
  else if (getCookie('auth0_spajs')) {
        // get cookie data
        let auth0_spajs_cookie_data = getCookie("auth0_spajs");
		var new_data=JSON.parse(auth0_spajs_cookie_data);
        // expire existing cookie
        document.cookie = "auth0_spajs=; domain=." +
            location.hostname.split('.').reverse()[1] + "." +
            location.hostname.split('.').reverse()[0] + "; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
        // create new cookie with same cookie name reseting expiration clock.
        document.cookie = "auth0_spajs=" + auth0_spajs_cookie_data + ";secure; domain=." +
            location.hostname.split('.').reverse()[1] + "." +
            location.hostname.split('.').reverse()[0] + "; path=/; max-age=" + 45 * 24 * 60 * 60;			
			
	jQuery("#MG2login").hide();
	 jQuery("#userhandler").show();
	 jQuery("#MG2Detail").show();
    jQuery(".has-spinner").buttonLoader('stop');
	// jQuery(".auth0_user").css({"min-height":"34px","max-height":"34px"});
	// jQuery(".dropbtn ").css({"min-width":"auto"});
	jQuery("#MG2UserName").animate().text(short_text("Hi, "+new_data.given_name,12));
    jQuery("#MG2Detail").show();
    jQuery("#MG2UserName").show();	
    //jQuery("#MG2UserName").animate({width:jQuery("#MG2Detail").width()+10+'px'},1000).text(short_text("Hi, "+new_data.given_name,12));			
	jQuery("#MG2UserName ").css({"width":"auto","overflow": "hidden"}); 
}
  else {
	jQuery("#MG2Detail").hide();
	jQuery("#MG2UserName").hide();
    jQuery("#userhandler").show();
    jQuery("#MG2login").show();
    jQuery("#MG2Detail").hide();
    jQuery(".has-spinner").buttonLoader('stop');
  }
});     
  	 /*
 if(!getCookie("act_cmplt") && !getCookie("page_cancel") && getCookie("auth0_sub"))
 {

userid_auth0=getCookie("auth0_sub");				
		jQuery.ajax({
           url: "https://api.khaleejtimes.com/json/auth0_complete_profile.php", 
           type: "POST",            
            async: false,
            Timeout: 30000,           
           data: {'func': 'get_profile_c'}, 
           success:function (profiles) {  
                jQuery("body").append(profiles);   
				jQuery('#profileModal').modal('show');	
				waituntilcomplete=true; 				
            }
         })
		 
 }  
  */  
async function isprofilecompleted(userprofiles,returntype) {
    return new Promise((resolve, reject) => {        
        jQuery.ajax({
           url: "https://api.khaleejtimes.com/json/auth0_complete_profile.php", 
           type: "POST",            
            async: false,
            Timeout: 30000,      
            jsonp: "jsoncallback",
           data: {'func': 'auth0_token',userid:userprofiles,returntype:returntype,ntype:'check_user'}, 
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


async function getprofile(userid_auth0) {
    return new Promise((resolve, reject) => {        
        jQuery.ajax({
           url: "https://api.khaleejtimes.com/json/auth0_complete_profile.php", 
           type: "POST",            
            async: false,
            Timeout: 30000,           
           data: {'func': 'get_profile_c', 'user_id' : userid_auth0, 'test' : 'n'}, 
           success:function (profiles) {  
            resolve(profiles);                   
            },
            error: (profiles) => {
                reject(profiles);
            }
         })     
})
}


async function checkbcode(returntype) {
return new Promise((resolve, reject) => {
	jQuery.ajax({
           url: "https://api.khaleejtimes.com/json/auth0_complete_profile.php", 
           type: "POST",            
            async: false,
            Timeout: 30000,           
           data: {'func': 'check_barcode'}, 
           success:function (profiles) {  
            resolve(profiles);                   
            },
            error: (profiles) => {
                reject(profiles);
            }
         })	
})
}
//20230418 MCD: get cookie by name function
//20230510 MCD: Moved getCookie function outside of engage onLoggedIn event
function getCookie(cookieName) {
    let cookie = {};
    document.cookie.split(';').forEach(function(el) {
    let [key,value] = el.split('=');
    cookie[key.trim()] = value;
    })
    return cookie[cookieName];
}


function get_auth_user(user_id)
{
	jQuery.ajax({
   url: "https://api.khaleejtimes.com/json/read_auth0_user.php", 
   type: "GET", 
   dataType: "jsonp",jsonp: 'jsoncallback',
   data: {user_id:getCookie('auth0_sub')},
   success: function (result) { 
	    user_names = '{"given_name": "' + result.given_name + '",'; 
		user_names += '"family_name": "' + result.family_name + '",';	
		//user_names += '"name": "' + result.name + '"}';
		 user_names += '"user_hashemail": "' + SHA256(result.email) + '",';
		user_names += '"user_email": "' + result.email + '"}'; 
	   document.cookie = "auth0_spajs=" + user_names + ";secure; domain=" + location.hostname + "; path=/; max-age=" + 45*24*60*60;
	   let userinfo = getCookie("auth0_spajs");
	   let userinfo_json_obj = JSON.parse(userinfo); 
	   //document.getElementById('MG2UserName').textContent = "Hi, " + short_text(`${userinfo_json_obj.given_name}`,9);
       document.getElementById('MG2UserName').textContent = "Hi, " + short_text(`${userinfo_json_obj.given_name}`,9);
       console.log("Naivga: line 212");
    },
    error: function (err) {
    // check the err for error details
    }
 }); // ajax call closing
}

function short_text(auth0_text, count){
    return auth0_text.slice(0, count) + (auth0_text.length > count ? "..." : "");
}

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
	/*
jQuery.ajax({
   url: "https://navstage.khaleejtimes.com/wp-admin/admin-ajax.php", 
   type: "POST", 
   dataType: "json",
   data: {'action': 'google_one',sdata:window.identity},  
   success: function (result) {     
    },
    error: function (err) {
  
    }
 }); 
		*/
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
async function  showAuthInfo() {
    if (window.isAuthenticated) { 
		//let iscompleted = await isprofilecompleted('auth0|'+window.identity.sub,'contact');  
    
           // if(iscompleted=='null' && getCookie("page_cancel")!==window.identity.sub && window.location.href.indexOf("complete_profile") == -1 && !getCookie("auth0_sub"))
              //{             
             //window.location.href="complete_profile";
             // } 
		 user_names = '{"given_name": "' + window.identity.given_name + '",'; 
		user_names += '"family_name": "' + window.identity.family_name + '",';	
		//user_names += '"name": "' + result.name + '"}';
		 user_names += '"user_hashemail": "' + SHA256(identity.email) + '",';
		user_names += '"user_email": "' + window.identity.email + '"}'; 	
		document.cookie = "auth0_sub=auth0|" + window.identity.sub + ";secure; domain=" + location.hostname + "; path=/; max-age=" + 45*24*60*60;
		 document.cookie = "auth0_spajs=" + user_names + ";secure; domain=" + location.hostname + "; path=/; max-age=" + 45*24*60*60;
		jQuery("#MG2login").css("display","none");
		//jQuery("#buttonDiv").css("display","none");
		jQuery("#MG2UserName").css("display","");
		jQuery("#MG2logout").css("display","");
		jQuery("[data-mg2-action='hideloggedout']").css("display","");	
		document.getElementById('MG2UserName').textContent = "Hi, " + short_text(`${window.identity.given_name}`,9);
		 window.isAuthenticated = true;
		sync_user();  
		
	
    } 
}
if (!getCookie("auth0_sub")) {                       
window.onload = function () {
    if(!getCookie("gsi_session") && !getCookie("auth0_sub")){
    window.isAuthenticated = false;	
    showAuthInfo();
    google.accounts.id.initialize({
      client_id: "845685274514-tpqheobgj0e6sd3mvtt1q046eaqo3ial.apps.googleusercontent.com",
		hl: 'EN',	
		//use_fedcm_for_prompt:true,		
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
                google.accounts.id.prompt();				
            }	
    const googlePromptFrame = document.querySelector("#credential_picker_container iframe");		
    if(googlePromptFrame) 
	{
	var left= jQuery("body").width()-jQuery("#credential_picker_container iframe").width()+"px";		
	jQuery("#credential_picker_container").css({"position": "absolute", "left": left,"top": "", "width": "0","height": "0", "z-index": "1001"});
	var gvalue=1;
	document.cookie = "gsi_session="+ gvalue+";domain=" + location.hostname + ";path=/;";	
	}	
	
	jQuery("#credential_picker_iframe iframe").ready(function (){
	var gvalue=1;
	document.cookie = "gsi_session="+ gvalue+";domain=" + location.hostname + ";path=/;";		
	});
  
	
		});	  
	}	
}

}


function SHA256(s){var chrsz=8;var hexcase=0;function safe_add(x,y){var lsw=(x&0xFFFF)+(y&0xFFFF);var msw=(x>>16)+(y>>16)+(lsw>>16);return(msw<<16)|(lsw&0xFFFF);}
function S(X,n){return(X>>>n)|(X<<(32-n));}
function R(X,n){return(X>>>n);}
function Ch(x,y,z){return((x&y)^((~x)&z));}
function Maj(x,y,z){return((x&y)^(x&z)^(y&z));}
function Sigma0256(x){return(S(x,2)^S(x,13)^S(x,22));}
function Sigma1256(x){return(S(x,6)^S(x,11)^S(x,25));}
function Gamma0256(x){return(S(x,7)^S(x,18)^R(x,3));}
function Gamma1256(x){return(S(x,17)^S(x,19)^R(x,10));}
function core_sha256(m,l){var K=new Array(0x428A2F98,0x71374491,0xB5C0FBCF,0xE9B5DBA5,0x3956C25B,0x59F111F1,0x923F82A4,0xAB1C5ED5,0xD807AA98,0x12835B01,0x243185BE,0x550C7DC3,0x72BE5D74,0x80DEB1FE,0x9BDC06A7,0xC19BF174,0xE49B69C1,0xEFBE4786,0xFC19DC6,0x240CA1CC,0x2DE92C6F,0x4A7484AA,0x5CB0A9DC,0x76F988DA,0x983E5152,0xA831C66D,0xB00327C8,0xBF597FC7,0xC6E00BF3,0xD5A79147,0x6CA6351,0x14292967,0x27B70A85,0x2E1B2138,0x4D2C6DFC,0x53380D13,0x650A7354,0x766A0ABB,0x81C2C92E,0x92722C85,0xA2BFE8A1,0xA81A664B,0xC24B8B70,0xC76C51A3,0xD192E819,0xD6990624,0xF40E3585,0x106AA070,0x19A4C116,0x1E376C08,0x2748774C,0x34B0BCB5,0x391C0CB3,0x4ED8AA4A,0x5B9CCA4F,0x682E6FF3,0x748F82EE,0x78A5636F,0x84C87814,0x8CC70208,0x90BEFFFA,0xA4506CEB,0xBEF9A3F7,0xC67178F2);var HASH=new Array(0x6A09E667,0xBB67AE85,0x3C6EF372,0xA54FF53A,0x510E527F,0x9B05688C,0x1F83D9AB,0x5BE0CD19);var W=new Array(64);var a,b,c,d,e,f,g,h,i,j;var T1,T2;m[l>>5]|=0x80<<(24-l % 32);m[((l+64>>9)<<4)+15]=l;for(var i=0;i<m.length;i+=16){a=HASH[0];b=HASH[1];c=HASH[2];d=HASH[3];e=HASH[4];f=HASH[5];g=HASH[6];h=HASH[7];for(var j=0;j<64;j++){if(j<16)W[j]=m[j+i];else W[j]=safe_add(safe_add(safe_add(Gamma1256(W[j-2]),W[j-7]),Gamma0256(W[j-15])),W[j-16]);T1=safe_add(safe_add(safe_add(safe_add(h,Sigma1256(e)),Ch(e,f,g)),K[j]),W[j]);T2=safe_add(Sigma0256(a),Maj(a,b,c));h=g;g=f;f=e;e=safe_add(d,T1);d=c;c=b;b=a;a=safe_add(T1,T2);}
HASH[0]=safe_add(a,HASH[0]);HASH[1]=safe_add(b,HASH[1]);HASH[2]=safe_add(c,HASH[2]);HASH[3]=safe_add(d,HASH[3]);HASH[4]=safe_add(e,HASH[4]);HASH[5]=safe_add(f,HASH[5]);HASH[6]=safe_add(g,HASH[6]);HASH[7]=safe_add(h,HASH[7]);}
return HASH;}
function str2binb(str){var bin=Array();var mask=(1<<chrsz)-1;for(var i=0;i<str.length*chrsz;i+=chrsz){bin[i>>5]|=(str.charCodeAt(i/chrsz)&mask)<<(24-i % 32);}
return bin;}
function Utf8Encode(string){string=string.replace(/\r\n/g,'\n');var utftext='';for(var n=0;n<string.length;n++){var c=string.charCodeAt(n);if(c<128){utftext+=String.fromCharCode(c);}
else if((c>127)&&(c<2048)){utftext+=String.fromCharCode((c>>6)|192);utftext+=String.fromCharCode((c&63)|128);}
else{utftext+=String.fromCharCode((c>>12)|224);utftext+=String.fromCharCode(((c>>6)&63)|128);utftext+=String.fromCharCode((c&63)|128);}}
return utftext;}
function binb2hex(binarray){var hex_tab=hexcase?'0123456789ABCDEF':'0123456789abcdef';var str='';for(var i=0;i<binarray.length*4;i++){str+=hex_tab.charAt((binarray[i>>2]>>((3-i % 4)*8+4))&0xF)+
hex_tab.charAt((binarray[i>>2]>>((3-i % 4)*8))&0xF);}
return str;}
s=Utf8Encode(s);return binb2hex(core_sha256(str2binb(s),s.length*chrsz));}