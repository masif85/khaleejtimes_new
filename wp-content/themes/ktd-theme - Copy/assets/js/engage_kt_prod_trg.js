/* ENGAGE LOGIN CODE */

jQuery("#MG2logout").click(function(){    
    function delete_cookie(name) {
        document.cookie = name + "=;path=/;expires=Thu, 01 Jan 1970 00:00:01 GMT";
        document.cookie = name + "=;path=/;domain=" + location.host + ";expires=Thu, 01 Jan 1970 00:00:01 GMT";
        document.cookie = name + "=;path=/;domain=." + location.host + ";expires=Thu, 01 Jan 1970 00:00:01 GMT";
        var arr = location.host.split(".");
        var rootdomain = arr[arr.length - 2] + "." + arr[arr.length - 1];
        document.cookie = name + "=;path=/;domain=" + rootdomain + ";expires=Thu, 01 Jan 1970 00:00:01 GMT";
        document.cookie = name + "=;path=/;domain=." + rootdomain + ";expires=Thu, 01 Jan 1970 00:00:01 GMT";
    }
    delete_cookie("igmAuth");
    delete_cookie("igmContent");
    delete_cookie("igmRegID");
    delete_cookie("auth0_spajs");
});

var mg2Logout = function (e) {
    function delete_cookie(name) {
        document.cookie = name + "=;path=/;expires=Thu, 01 Jan 1970 00:00:01 GMT";
        document.cookie = name + "=;path=/;domain=" + location.host + ";expires=Thu, 01 Jan 1970 00:00:01 GMT";
        document.cookie = name + "=;path=/;domain=." + location.host + ";expires=Thu, 01 Jan 1970 00:00:01 GMT";
        var arr = location.host.split(".");
        var rootdomain = arr[arr.length - 2] + "." + arr[arr.length - 1];
        document.cookie = name + "=;path=/;domain=" + rootdomain + ";expires=Thu, 01 Jan 1970 00:00:01 GMT";
        document.cookie = name + "=;path=/;domain=." + rootdomain + ";expires=Thu, 01 Jan 1970 00:00:01 GMT";
    }

    delete_cookie("igmAuth");
    delete_cookie("igmContent");
    delete_cookie("igmRegID");
    delete_cookie("auth0_spajs");
    delete_cookie("auth0_sub");
}

let loginButton = jQuery("[id=MG2login]");
let logoutButton =jQuery("[id=MG2logout]");
let myAccountButton = jQuery("[id=MG2MyAccount]");
let subscribeButton = jQuery("[id=MG2Subscribe]");
let currentDomainName = window.location.hostname;

function short_text(auth0_text, count){
    return auth0_text.slice(0, count) + (auth0_text.length > count ? "..." : "");
}

MG2Loader.init({
    plugins: [{
        name: "FP",
        initOptions: {
            version: "khaleejtimes",
            environment: "prod"
        }
    }, {
        name: "DL",
        initOptions: {
            version: "khaleejtimes",
            collectors: ["connext"],
            tagManager: "GTM",
            containerId: "GTM-M3JVS4X"
        }
    }, {
        name: "NXT",
        initOptions: {
            clientCode: "khaleejtimes",
            environment: "prod",
            siteCode: "KT",
            configCode: "KTConfig",
            debug: false,
            authSettings: {
                domain: "khaleejtimes.eu.auth0.com",
                client_id: "qgTRkI74FUM6zFHEao5WTNPk27z5fwfR",
                redirect_uri: "https://" + currentDomainName + "/accessing-account"
            },
            publicEventHandlers: {
                onNotAuthorized: function (eventData) {
                    loginButton.show();
                    subscribeButton.show();
                    myAccountButton.hide();
                    logoutButton.hide();
                },
                onAuthorized: function (eventData) {
                    loginButton.hide();
                    subscribeButton.show();
                    myAccountButton.show();
                    logoutButton.show();
                },
                onHasNoActiveSubscription: function (eventData) {
                    loginButton.hide();
                    subscribeButton.show();
                    myAccountButton.show();
                    logoutButton.show();
                },
                onHasAccessNotEntitled: function (eventData) {
                    loginButton.hide();
                    subscribeButton.show();
                    myAccountButton.show();
                    logoutButton.show();
                },
                onHasAccess: function (eventData) {
                    loginButton.hide();
                    subscribeButton.hide();
                    myAccountButton.show();
                    logoutButton.show();
                },
                onLoggedIn: function (eventData) {
                    loginButton.hide();
                    subscribeButton.hide();
                    myAccountButton.show();
                    logoutButton.show();
                    //20230418 MCD: Code added to work around 24 hr expiration of auth0_spajs local storage record"
                    //set cookie from auth0_spajs local storage
                    if (!document.cookie.match(/^(.*;)?\s*auth0_spajs\s*=\s*[^;]+(.*)?$/ || document.cookie.match(/^(.*;)?\s*auth0_sub\s*=\s*[^;]+(.*)?$/))) {
                        if (localStorage.getItem('@@auth0spajs@@::Og9eZ12aZ5kjqIdo8LFUrmzcqilVqKeE::default::openid profile email') != null) {
                            let auth0_local_storage_value = localStorage.getItem('@@auth0spajs@@::Og9eZ12aZ5kjqIdo8LFUrmzcqilVqKeE::default::openid profile email');
                            let json_obj = JSON.parse(auth0_local_storage_value);
                            let user_names = '';
                            if (json_obj.body.decodedToken.user.sub.search("apple") > -1) {
                                let apple_name = json_obj.body.decodedToken.user.name.split(' ');
                                user_names = '{ "given_name": "' + apple_name[0] + '",';
                                user_names += '"family_name": "' + apple_name[1] + '",';
                            }
                            else {
                                user_names = '{ "given_name": "' + json_obj.body.decodedToken.user.given_name + '",';
                                user_names += '"family_name": "' + json_obj.body.decodedToken.user.family_name + '",';
                            }
                            user_names += '"user_email": "' + json_obj.body.decodedToken.user.email + '",';
                            user_names += '"name": "' + json_obj.body.decodedToken.user.name + '"}';
                            document.cookie = "auth0_spajs=" + user_names + ";secure; domain=." +
                                location.hostname.split('.').reverse()[1] + "." +
                                location.hostname.split('.').reverse()[0] + "; path=/; max-age=" + 45 * 24 * 60 * 60;
							
								smartech('identify', '' + json_obj.body.decodedToken.user.email + '');
								smartech('dispatch', 'newsletter sub', {
									'email': '' + json_obj.body.decodedToken.user.email + ''
								});
							
                        }		
						
						
				
						
                    }
					
					let userinfo = getCookie("auth0_spajs");
						console.log(userinfo);
						let userinfo_json_obj = JSON.parse(userinfo);
                   
					
					
                    // 20230327 ERD: Apple login does not include given_name or family_name -- only name
                    //if (userinfo_json_obj.given_name && userinfo_json_obj.family_name && userinfo_json_obj.given_name != 'undefined' && userinfo_json_obj.family_name != 'undefined') {
					 if (userinfo_json_obj.given_name && userinfo_json_obj.given_name!= 'undefined') {	
                        document.getElementById('MG2UserName').textContent = "Hi, " + short_text(`${userinfo_json_obj.given_name}`, 8);
                    }
					else if (userinfo_json_obj.family_name && userinfo_json_obj.family_name!= 'undefined') {
						 document.getElementById('MG2UserName').textContent = "Hi, " + short_text(`${userinfo_json_obj.family_name}`, 8); 							 
					 }					
					else if (userinfo_json_obj.nickname && userinfo_json_obj.nickname != 'undefined') {
                        document.getElementById('MG2UserName').textContent = "Hi, " + `${userinfo_json_obj.nickname}`;
                        // document.getElementById('MG2UserName').textContent = "Hello, Reader";
                    }
                    else {
                        document.getElementById('MG2UserName').textContent = "Hi, Reader";
                    }
                }

            }
        }
    }]
});
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
if (!document.cookie.match(/^(.*;)?\s*auth0_sub\s*=\s*[^;]+(.*)?$/)) {
    if (document.cookie.match(/^(.*;)?\s*auth0_spajs\s*=\s*[^;]+(.*)?$/)) {
        document.cookie = "auth0_spajs=; domain=." +
            location.hostname.split('.').reverse()[1] + "." +
            location.hostname.split('.').reverse()[0] + "; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
        
        document.cookie = "auth0_spajs=; domain=" + location.hostname + "; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
    }

} else {
    //ELSE and IF statement added to get around safari and other browser ITP Policies
    if (document.cookie.match(/^(.*;)?\s*auth0_spajs\s*=\s*[^;]+(.*)?$/)) {
        // get cookie data
        let auth0_spajs_cookie_data = getCookie("auth0_spajs");
        // expire existing cookie
        document.cookie = "auth0_spajs=; domain=." +
            location.hostname.split('.').reverse()[1] + "." +
            location.hostname.split('.').reverse()[0] + "; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
        // create new cookie with same cookie name reseting expiration clock.
        document.cookie = "auth0_spajs=" + auth0_spajs_cookie_data + ";secure; domain=." +
            location.hostname.split('.').reverse()[1] + "." +
            location.hostname.split('.').reverse()[0] + "; path=/; max-age=" + 45 * 24 * 60 * 60;
		
    } else {
        if (document.cookie.match(/^(.*;)?\s*auth0_sub\s*=\s*[^;]+(.*)?$/)) {
            get_auth_user(getCookie("auth0_sub"));

            //document.cookie = "auth0_sub=; domain=." + 
            //location.hostname.split('.').reverse()[1] + "." + 
            //location.hostname.split('.').reverse()[0] + "; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/"; 
        }
    }
}
if (document.cookie.match(/^(.*;)?\s*auth0spajs\s*=\s*[^;]+(.*)?$/)) {
    document.cookie = "auth0spajs=; domain=." +
        location.hostname.split('.').reverse()[1] + "." +
        location.hostname.split('.').reverse()[0] + "; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";

}

function get_auth_user(user_id) {
    jQuery.ajax({
        url: "https://api.khaleejtimes.com/json/read_auth0_user.php",
        type: "GET",
        dataType: "jsonp", jsonp: 'jsoncallback',
        data: { user_id: getCookie('auth0_sub') },
        success: function (result) {
            user_names = '{ "given_name": "' + result.given_name + '",';
            user_names += '"family_name": "' + result.family_name + '",';
            //user_names += '"name": "' + result.name + '"}';
            user_names += '"user_email": "' + result.email + '"}';
            document.cookie = "auth0_spajs=" + user_names + ";secure; domain=" + location.hostname + "; path=/; max-age=" + 45 * 24 * 60 * 60;
            let userinfo = getCookie("auth0_spajs");
            let userinfo_json_obj = JSON.parse(userinfo);
            //document.getElementById('MG2UserName').textContent = "Hi, " + short_text(`${userinfo_json_obj.given_name}`,9);
            document.getElementById('MG2UserName').textContent = "Hi, " + short_text(`${userinfo_json_obj.given_name}`, 8);
        },
        error: function (err) {
            // check the err for error details
        }
    }); // ajax call closing
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
function showAuthInfo() {
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
    } 
}
if (! getCookie("auth0_sub")) {                       
window.onload = function () {
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
		 if (notification.isNotDisplayed() || notification.isSkippedMoment()) {
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

if(! getCookie("gsi_session"))
{
		var g_session=new Date();
		g_session.setTime(g_session.getTime()+(1*24*60*60*1000));
		//var gexpires = "expires="+g_session.toGMTString();
		//var valuez=getCookieValue('gsi_session');	
	var gexpires ="";
		var gvalue=1;
		document.cookie = "gsi_session="+ gvalue+";"+gexpires+";path=/";		
}