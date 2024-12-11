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



//add_filter('wpcf7_skip_spam_check', '__return_true');
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

add_action( 'wp_ajax_get_section_articles', 'get_section_articles' );
add_action( 'wp_ajax_nopriv_get_section_articles', 'get_section_articles' );

add_action( 'wp_ajax_get_section_listing', 'get_section_listing' );
add_action( 'wp_ajax_nopriv_get_section_listing', 'get_section_listing' );

add_action( 'wp_ajax_top_news_left_netcore', 'top_news_left_netcore' );
add_action( 'wp_ajax_nopriv_top_news_left_netcore', 'top_news_left_netcore' );

add_action( 'wp_ajax_recommended_netcore', 'recommended_netcore' );
add_action( 'wp_ajax_nopriv_recommended_netcore', 'recommended_netcore' );

add_action( 'wp_ajax_article_mid_netcore', 'article_mid_netcore' );
add_action( 'wp_ajax_nopriv_article_mid_netcore', 'article_mid_netcore' );

add_action( 'wp_ajax_section_netcore', 'section_netcore' );
add_action( 'wp_ajax_nopriv_section_netcore', 'section_netcore' );


function section_netcore()
{
$parsed_url = $_POST['url']; 
$slug = substr($parsed_url, 1 );
$section= get_the_title(url_to_postid($slug));
$excludes="";
$set="";
$boxTokenId = '';
if( isset( $_COOKIE['boxx_token_id'] ) ){
  $boxTokenId = $_COOKIE['boxx_token_id'];
}

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://loki.boxx.ai/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
                        "client_id": "x9vk",
                        "access_token": "478b112d-8bd1-49ad-ad97-1f44b023705c",
                        "channel_id": "DyMq",
                        "is_internal": false,
                        "is_boxx_internal": false,
                        "rec_type": "trending",
                        "no_cache": true,
                        "related_action_as_view": true,
                        "related_action_type": "view",
                        "transaction_window": "24",
                        "query": {
                          "userid": "",
                          "boxx_token_id": "'.$boxTokenId.'",
                          "item_filters": {"n_days_old" : {"$lte": 3},"category" : "'.$section.'"},
                          "related_products": [],
                          "exclude": [],
                          "num": 4,
                          "offset": 10,
                          "get_product_properties": true,
                          "get_product_aliases": false
                        }
                          }',
                          CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                          ),
));

$response = curl_exec($curl);
curl_close($curl);
$response_arr = json_decode( $response, true );
$popular       = $response_arr['result'];


$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://loki.boxx.ai/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{   
                         "client_id": "x9vk",
                          "access_token": "478b112d-8bd1-49ad-ad97-1f44b023705c",
                          "channel_id": "DyMq",
                          "is_internal": false,
                          "is_boxx_internal": false,
                          "rec_type": "trending",
                          "no_cache": true,
                          "related_action_as_view": true,
                          "related_action_type": "view",
                          "transaction_window": "24",
                          "query": {
                            "userid": "",
                            "boxx_token_id": "",
                            "item_filters": {"n_days_old" : {"$lte": 4}},
                            "related_products": [],
                            "exclude": [],
                            "num": 4,
                            "offset": 5,
                            "get_product_properties": true,
                            "get_product_aliases": false
                          }
                            }',
        CURLOPT_HTTPHEADER => array(
          'Content-Type: application/json',
        ),
      ));

  $response = curl_exec($curl);
  curl_close($curl);
  $response_arr = json_decode( $response, true );
  $entire       = $response_arr['result'];
  $data='';
$data.='
<section class="popular-section">
    <div class="row">
      <div class="col-12">
        <div class="most-popuplar-ongoing-viral-outer">
        <div class="main-side-bartitle-mob"><h5>Most Popular In</h5></div>
          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true"><span>Most Popular in</span> '.$section.'</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false" tabindex="-1"><span>Most Popular in</span> Khaleej Times</button>
            </li>
          </ul>          
        <div class="tab-content" id="myTabContent">            
            <div class="tab-pane fade active show" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">            
            <div class="row">';
            $i=1;
            foreach($popular as $bitem):
        $data.='
              <div class="col-12 col-lg-3 col-md-6 col-sm-12 tab-box">
                <ul>
                  <li><span>'.$i.'</span></li>
                  <li><h4><a href="'.$bitem['properties']['link'].'">'.$bitem['properties']['title'].'</a></h4></li>
                </ul>
              </div>'; 
              $i++;
              endforeach;      
         
            $data.=' </div>
            </div>
        <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
              <div class="row">';
              foreach($entire as $eitem):
              $image=get_image_type($eitem['properties']['image'],250);
      $imageurl=str_replace('original', 'cropresize', $eitem['properties']['image'])."&source=false&q=75&crop_w=0.77125&crop_h=0.91556&$image&x=0.1375&y=0.02222/";   
            $data.='
                <div class="col-12 col-lg-3 col-md-6 col-sm-12 tab-box">
                  <ul>                   
                    <li><div class="entire-website-thumb image-zoom"><img src="'.$imageurl.'"  onerror="console.log(this.srcset); this.srcset = https://image.khaleejtimes.com/?uuid=6b5e4369-15ca-52b5-a3cd-432ee563d856&amp;function=original&amp;type=preview; console.log(this.srcset);"></div></li>
                    <li><h4><a href="'.$eitem['properties']['link'].'">'.$eitem['properties']['title'].'</a></h4></li>
                  </ul>
                </div>';
            endforeach;
             $data.='
              </div>
            </div>
           </div>
        </div>
      </div>
    </div>
</section>';
echo $data;
wp_die(); 
}

function article_mid_netcore()
{
$excludes="";
$set="";
$boxTokenId = '';
if( isset( $_COOKIE['boxx_token_id'] ) ){
  $boxTokenId = $_COOKIE['boxx_token_id'];
}

if( isset( $_COOKIE['boxx_token_id'] ) ){
  $boxTokenId = $_COOKIE['boxx_token_id'];
}
if(!isset($_GET['skip']))
{
  $set_start=3;
  $wdms='$lte';
}
else
{
$set_start=$_GET['skip']+3;  
$wdms='$gte'; 
}
$data=explode("/",$_SERVER['REQUEST_URI']); 
$section= UCFIRST(str_replace('-', ' ', $data[1]));
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://loki.boxx.ai/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 10,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
                "client_id": "x9vk",
                "access_token": "478b112d-8bd1-49ad-ad97-1f44b023705c",
                "channel_id": "DyMq",
                "is_internal": false,
                "is_boxx_internal": false,
                "rec_type": "boxx",
                "no_cache": true,
                "related_action_as_view": true,
                "related_action_type": "view",
                "transaction_window": "24",
                "query": {
                  "userid": "",
                  "boxx_token_id": "'.$boxTokenId.'",
                  "item_filters": {"n_days_old" : {"$lte": '.$set_start.'}},
                  "related_products": [],
                  "exclude": [],
                  "num": 5,
                  "offset": 20,
                  "get_product_properties": true,
                  "get_product_aliases": false
                }
                  }',
                  CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                  ),
  ));
$response = curl_exec($curl);
curl_close($curl);
$response_arr = json_decode( $response, true );
$items = $response_arr['result'];
//"item_filters": {"n_days_old" : {"$lte": '.$set_start.'},"category" : "'.$section.'"},
$data='<div class="recommend-wrap-mobile-lb"> 
   <h3>Recommended For You</h3> </div>
 <div class="recommended-mobile-with-image">';

 foreach($items as $item):
      $image=get_image_type($item['properties']['image'],250);
      $imageurl=str_replace('original', 'cropresize', $item['properties']['image'])."&source=false&q=75&crop_w=0.77125&crop_h=0.91556&$image&x=0.1375&y=0.02222/";   
        $data.='<div class="recommended-mobile-with-image-box">  
           <a href="'.$item['properties']['link'].'?utm_source=netcore&utm_medium=art-rcmd-api&utm_campaign=recommended-inarticle-mob" class="g4a_amn_track_sidebar">  
          <img src="'.$imageurl.'" onerror="console.log(this.srcset); this.srcset = https://image.khaleejtimes.com/?uuid=6b5e4369-15ca-52b5-a3cd-432ee563d856&amp;function=original&amp;type=preview; console.log(this.srcset);" style="aspect-ratio: 3 / 2;">
           <h4>'.$item['properties']['title'].'</h4>
           </a>
        </div>';
  endforeach;  
  $data.='</div><div class="recommend-wrap-mobile-lb2">&nbsp;</div>';
echo $data;
wp_die(); 

}

function recommended_netcore()
{
$excludes="";
$set="";
$boxTokenId = '';
if( isset( $_COOKIE['boxx_token_id'] ) ){
  $boxTokenId = $_COOKIE['boxx_token_id'];
}

if( isset( $_COOKIE['boxx_token_id'] ) ){
  $boxTokenId = $_COOKIE['boxx_token_id'];
}

//$set=array(0=>1,1=>2,2=>35,3=>40,4=>45);
if(!isset($_GET['skip']))
{
  $set_start=3;
  $wdms='$lte';
}
else
{
$set_start=$_GET['skip']+3;  
$wdms='$gte'; 
}

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://loki.boxx.ai/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
  "client_id": "x9vk",
  "access_token": "478b112d-8bd1-49ad-ad97-1f44b023705c",
  "channel_id": "DyMq",
  "is_internal": false,
  "is_boxx_internal": false,
  "rec_type": "boxx",
  "no_cache": true,
  "related_action_as_view": true,
  "related_action_type": "view",
  "transaction_window": "24",
  "query": {
    "userid": "",
    "boxx_token_id": "'.$boxTokenId.'",
    "item_filters": {"n_days_old" : {"'.$wdms.'": '.$set_start.'}
    },
    "related_products": [],
    "exclude": [],
    "num": 5,
    "offset": 20,
    "get_product_properties": true,
    "get_product_aliases": false
  }
    }',
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json',
    ),
  ));

  $response = curl_exec($curl);
  curl_close($curl);
  $response_arr = json_decode( $response, true );
  $items      = $response_arr['result'];
$data='';

 foreach($items as $item):
      $image=get_image_type($item['properties']['image'],250);
      $imageurl=str_replace('original', 'cropresize', $item['properties']['image'])."&source=false&q=75&crop_w=0.77125&crop_h=0.91556&$image&x=0.1375&y=0.02222/";
                 $data.='<article class="top-list-thumb-stories">
                    <div class="post-title-rows">';
             if($item['properties']['image']):
               $data.=' <div class="thumb-with-image image-zoom"> <a href="'.$item['properties']['link'].'?utm_source=netcore&utm_medium=art-rcmd-api&utm_campaign=recommended-inarticle-dt" class="g4a_amn_track_sidebar"><img src="
      '.$imageurl.'" onerror="console.log(this.srcset); this.srcset = https://image.khaleejtimes.com/?uuid=6b5e4369-15ca-52b5-a3cd-432ee563d856&amp;function=original&amp;type=preview; console.log(this.srcset);"> </a>
                      </div>';

              endif;              
                      $data.=' <div class="heading">
                        <h4> <a href="'.$item['properties']['link'].'?utm_source=netcore&utm_medium=art-rcmd-api&utm_campaign=recommended-inarticle-dt" class="g4a_amn_track_sidebar">'.$item['properties']['title'].'</a> </h4>
                      </div>
                    </div>
                  </article>';
  endforeach;   
echo $data;
wp_die(); 

}

function top_news_left_netcore()
{

  $excludes="";
  $set="";
  $boxTokenId = '';
  if( isset( $_COOKIE['boxx_token_id'] ) ){
    $boxTokenId = $_COOKIE['boxx_token_id'];
  }
  if(!isset($_GET['skip']))
  {
    $set_start=2;
     $wdms='$lte';
  }
  else
  {
  $set_start=$_GET['skip']+2;  
  $wdms='$gte'; 
  }

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://loki.boxx.ai/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
      "client_id": "x9vk",
      "access_token": "478b112d-8bd1-49ad-ad97-1f44b023705c",
      "channel_id": "DyMq",
      "is_internal": false,
      "is_boxx_internal": false,
      "rec_type": "trending",
      "no_cache": true,
      "related_action_as_view": true,
      "related_action_type": "view",
      "transaction_window": "24",
      "query": {
        "userid": "",
        "boxx_token_id": "'.$boxTokenId.'",
        "item_filters": {"n_days_old" : {"'.$wdms.'": '.$set_start.'}},
        "related_products": [],
        "exclude": [],
        "num": 3,
        "offset": 25,
        "get_product_properties": true,
        "get_product_aliases": false
      }
    }',
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json',
    ),
  ));
  $response = curl_exec($curl);
  curl_close($curl);
  $response_arr = json_decode( $response, true );
  $items       = $response_arr['result'];
$data='';
        foreach($items as $item):      
          $image=get_image_type($item['properties']['image'],450);
      $imageurl=str_replace('original', 'cropresize', $item['properties']['image'])."&source=false&q=75&crop_w=0.77125&crop_h=0.91556&$image&x=0.1375&y=0.02222/";

          $data.='<div class="arttopstrywrap-list-dt">
                                    <a href="'.$item['properties']['link'].'?utm_source=netcore&utm_medium=art-rcmd-api&utm_campaign=top-stories-inarticle" title="'.$item['properties']['title'].'">
                                    <div class="thumb-with-image image-zoom">
                                   <img src="'.$imageurl.'" onerror="console.log(this.srcset); this.srcset = https://image.khaleejtimes.com/?uuid=6b5e4369-15ca-52b5-a3cd-432ee563d856&amp;function=original&amp;type=preview; console.log(this.srcset);" style="aspect-ratio: 4 / 3;">
                                    </div>
                                    <h3>'.$item['properties']['title'].'</h3></a>
                                  </div>';
          endforeach;            
  echo $data;
wp_die(); 
}

function get_image_type($image="",$size)
{

$data=imageresize($image,(int)$size);
// if*()
   return "width=".$data[0]."&height=".$data[1]."";
}


function imageresize($imageFile="",$size="")
{

list($originalWidth, $originalHeight) = getimagesize($imageFile);
$ratio = $originalWidth / $originalHeight;
$targetWidth = $targetHeight = min($size, max($originalWidth, $originalHeight));

if ($ratio < 1) {
    $targetWidth = $targetHeight * $ratio;
} else {
    $targetHeight = $targetWidth / $ratio;
}

$srcWidth = $originalWidth;
$srcHeight = $originalHeight;
$srcX = $srcY = 0;
return array($targetWidth,$targetHeight);
}
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
      //  CURLOPT_URL => "https://khaleejtimes.eu.auth0.com/api/v2/users/$user_auth0",
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
      //  CURLOPT_URL => "https://khaleejtimes.eu.auth0.com/api/v2/users/$user_auth0",
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

/**
 * Get Section Data
 */

function get_section_articles(){
  $main_section = $_POST['main_section'];
  $section_uuid=$_POST['section_uuid'];
  /*if( $main_section == 'life-living' ){
     $search_section="Life%20and%20living";
    }
    else
    {
      $search_section=$main_section;
    }*/

  $section    = $_POST['section'];  
  $count = 2;
  if( !empty( $_POST['count'] ) || $_POST['count'] != 0 ){
    $count = $_POST['count'];
  }

  $item_filters = '{}';
  if( !empty($section) ){
    $section = ucfirst( $section );

    if( $section == 'uae' ){
      $section = strtoupper( $section );
    }
  }

  if(strtolower($_POST['main_section'])==strtolower($_POST['section']))
  {
    $q="";
  }
  else
  {
    $q="&q='{$section}'";
  }

  $data = array();
  $username = 'ktd';
  $password = 'ycvyxveQyR72xaENKK@QqBcB';

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://xlibris.public.prod.oc.ktd.infomaker.io:8443/opencontent/search?limit={$count}&start=0&contenttype=Article$q&CustomerContentSubType=ARTICLE&Status=usable&sort.Pubdate.ascending=false&sort.indexfield=Pubdate&ConceptSectionUuids={$section_uuid}",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_USERPWD => $username . ":" . $password,
  ));

  $responseOneContent = curl_exec($curl);
  curl_close($curl);

  $responseOneContent = json_decode( $responseOneContent, true );
  $html = '';

  if( isset( $responseOneContent['hits']['hits'] ) ){
    $articles = $responseOneContent['hits']['hits'];

    foreach ($articles as $key => $article) {
      $class = '';
      $articleData = array_shift( $article['versions'] );

      $articleData = $articleData['properties'];

      $uuid        = array_shift( $articleData['uuid'] );
      $title       = array_shift( $articleData['Headline'] );
      $permalink   = get_permalinks_sections( $uuid );
      $sectionName = array_shift( $articleData['Section'] );
      $teaserbody  = $articleData['TeaserBody'][0];
      $imageUUID   = array_shift( $articleData['TeaserImageUuids'] );

      $image=get_image_type("https://image.khaleejtimes.com/?uuid=$imageUUID&function=original&type=preview",650);
      $image2=get_image_type("https://image.khaleejtimes.com/?uuid=$imageUUID&function=original&type=preview",750);
      $imagelink="https://image.khaleejtimes.com/?uuid=$imageUUID&function=cropresize&type=preview&source=false&q=75&crop_w=0.77125&crop_h=0.91556&$image&x=0.1375&y=0.02222/";
      $imagelink2="https://image.khaleejtimes.com/?uuid=$imageUUID&function=cropresize&type=preview&source=false&q=75&crop_w=0.77125&crop_h=0.91556&$image2&x=0.1375&y=0.02222/";
      if( $main_section == 'uae' ){
        if( $key == 0 ){
          $html = '<div class="col-12 col-lg-4 board-col-xs-12 board-col-sm-12 board-col-md-4 board-col-lg-4 uae-stories-outer-left">
            <div class="rendered_board_template_widget">
            <section class="every_board">
              <div class="row align-items-stretch top-stories-outer-left">
              <div class="col-12 board-col-xs-12 board-col-sm-12 board-col-md-4 board-col-lg-4">
                <div class="rendered_board_article content-size-xs-12 content-size-sm-12 content-size-md-4 content-size-lg-4">
                <!-- 2024 notice teaser with image -->
                <div class="col-lg-12 col-md-12 col-12" data-uuid="'.$uuid.'">
                  <div class="post-title-rows">
                  <div class="thumb-with-image image-zoom">
                    <a href="'.$permalink.'">
                   <img class="img-fluid lazyload" data-srcset="'.$imagelink2.'" srcset="'.$imagelink2.'" src="'.$imagelink2.'" onerror="console.log(this.srcset); this.srcset = https://image.khaleejtimes.com/?uuid=6b5e4369-15ca-52b5-a3cd-432ee563d856&function=original&type=preview; console.log(this.srcset);" style="width: 100%; height: auto;" alt="'.$title.'">
                    </a>
                  </div>
                  <h4>
                    <a href="'.$permalink.'">'.$title.'</a>
                  </h4>
                  <p>
                    <a href="'.$permalink.'">'.$teaserbody.'</a>
                  </p>
                  </div>
                </div>
                </div>
              </div>
              </div>
            </section>
            </div>
          </div>
          <div class="col-12 col-lg-4 board-col-xs-12 board-col-sm-12 board-col-md-4 board-col-lg-4 uae-stories-outer-left-small-main">
            <div class="rendered_board_template_widget uae-stories-outer-left-small">
            <section class="every_board">
              <div class="row align-items-stretch">
              <div class="col-12 board-col-xs-12 board-col-sm-12 board-col-md-4 board-col-lg-4">';
        }
        else{
          $html .= '<div class="rendered_board_article content-size-xs-12 content-size-sm-12 content-size-md-4 content-size-lg-4">
            <!-- side-by-side teaser -->
            <article class="uae-list-thumb-stories">
            <div class="post-title-rows">
              <div class="thumb-with-image image-zoom">
              <a href="'.$permalink.'">
                 <img class="img-fluid lazyload" data-srcset="'.$imagelink.'" src="'.$imagelink.'" srcset="'.$imagelink.'"  onerror="console.log(this.srcset); this.srcset = https://image.khaleejtimes.com/?uuid=6b5e4369-15ca-52b5-a3cd-432ee563d856&function=original&type=preview; console.log(this.srcset);" style="width: 100%; height: auto;" alt="'.$title.'">
              </a>
              </div>
              <div class="heading">
              <h4>
                <a href="'.$permalink.'">'.$title.'</a>
              </h4>
              </div>
            </div>
            </article>
          </div>';
        }
      }

      if( $main_section == 'world' ){
        if( $key == 0 ){
          $html = '<div class="col-12 col-lg-5 board-col-xs-12 board-col-sm-6 board-col-md-3 board-col-lg-3">
            <div class="rendered_board_article content-size-xs-12 content-size-sm-6 content-size-md-3 content-size-lg-3">
              <!-- 2024 notice teaser with image -->
              <div class="col-lg-12 col-md-12 col-12 world-stories-outer-left" data-uuid="'.$uuid.'">
                <div class="post-title-rows">
                  <div class="thumb-with-image image-zoom">
                    <a href="'.$permalink.'">
                       <img class="img-fluid lazyload" data-srcset="'.$imagelink2.'" srcset="'.$imagelink2.'" src="'.$imagelink2.'" onerror="console.log(this.srcset); this.srcset = https://image.khaleejtimes.com/?uuid=6b5e4369-15ca-52b5-a3cd-432ee563d856&function=original&type=preview; console.log(this.srcset);"   style="width: 100%; height: auto;" alt="'.$title.'">
                    </a>
                  </div>
                  <h4><a href="'.$permalink.'">'.$title.'</a></h4>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-7 board-col-xs-12 board-col-sm-6 board-col-md-4 board-col-lg-4 world-stories-outer-left-small-main world-stories-outer-left-small">';
        }
        else{
          $html .= '<div class="rendered_board_article content-size-xs-12 content-size-sm-6 content-size-md-4 content-size-lg-4">
            <!-- notice teaser no image -->
            <div class="row world-list-without-thumb-stories-main">
              <article class="world-list-without-thumb-stories" data-uuid="'.$uuid.'">
                <div class="post-title-rows">
                  <div class="heading">
                    <h4><a href="'.$permalink.'">'.$title.'</a></h4>
                  </div>
                </div>
              </article>
            </div>
          </div>';
        }
      }

      if( $main_section == 'life-living' || $main_section == 'sports' || $main_section == 'lifestyle' ){
        if( $key == 0 ){
          $html = '<div class="col-12 col-lg-5 board-col-xs-12 board-col-sm-6 board-col-md-3 board-col-lg-3">
            <div class="rendered_board_article content-size-xs-12 content-size-sm-6 content-size-md-3 content-size-lg-3">
              <!-- 2024 notice teaser with image -->
              <div class="col-lg-12 col-md-12 col-12 world-stories-outer-left" data-uuid="'.$uuid.'">
                <div class="post-title-rows">
                  <div class="thumb-with-image image-zoom">
                    <a href="'.$permalink.'">
                       <img class="img-fluid lazyload" data-srcset="'.$imagelink2.'" srcset="'.$imagelink2.'" src="'.$imagelink2.'"  onerror="console.log(this.srcset); this.srcset = https://image.khaleejtimes.com/?uuid=6b5e4369-15ca-52b5-a3cd-432ee563d856&function=original&type=preview; console.log(this.srcset);"   style="width: 100%; height: auto;" alt="'.$title.'">
                    </a>
                  </div>
                  <h4><a href="'.$permalink.'">'.$title.'</a></h4>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-7 board-col-xs-12 board-col-sm-6 board-col-md-4 board-col-lg-4 world-stories-outer-left-small-main world-stories-outer-left-small">';
        }
        else{
          $html .= '<div class="rendered_board_article content-size-xs-12 content-size-sm-6 content-size-md-4 content-size-lg-4">
            <!-- notice teaser no image -->
            <div class="row world-list-without-thumb-stories-main">
              <article class="world-list-without-thumb-stories" data-uuid="'.$uuid.'">
                <div class="post-title-rows">
                  <div class="heading">
                    <h4><a href="'.$permalink.'">'.$title.'</a></h4>
                  </div>
                </div>
              </article>
            </div>
          </div>';
        }
      }

      if( $main_section == 'business' ){
        if( $key == 0 ){
          $html .= '<div class="rendered_board_template_widget">
                <section class="every_board">
                  <div class="row align-items-stretch">';
        }

        if( $key == 3 ){
          $class = 'biz-lt-thmb-cln-last';
        }

        if( $key < 4 ){
          $html .= '<div class="col-12 col-lg-3 board-col-xs-6 board-col-sm-6 board-col-md-2 board-col-lg-2 '.$class.'">
            <div class="rendered_board_article content-size-xs-6 content-size-sm-6 content-size-md-2 content-size-lg-2">
              <!-- 2024 notice teaser with image -->
              <div class="col-lg-12 col-md-12 col-12" data-uuid="'.$uuid.'">
                <div class="post-title-rows">
                  <div class="thumb-with-image image-zoom">
                    <a href="'.$permalink.'">
                       <img class="img-fluid lazyload" data-srcset="'.$imagelink2.'" srcset="'.$imagelink2.'" src="'.$imagelink2.'"  onerror="console.log(this.srcset); this.srcset = https://image.khaleejtimes.com/?uuid=6b5e4369-15ca-52b5-a3cd-432ee563d856&function=original&type=preview; console.log(this.srcset);"   style="width: 100%; height: auto;" alt="'.$title.'">
                    </a>
                  </div>
                  <h4>
                    <a href="'.$permalink.'">'.$title.'</a>
                  </h4>
                </div>
              </div>
            </div>
          </div>';
        }

        if( $key == 4 ){
          $html .= '</div><div class="row align-items-stretch">
            <div class="col-12 col-lg-6 board-col-xs-6 board-col-sm-6 board-col-md-5 board-col-lg-5 heading biz-lt-no-thmb-cln-fst">';
        }

        if( $key > 3 ){
          $html .= '<div class="rendered_board_article content-size-xs-6 content-size-sm-6 content-size-md-5 content-size-lg-5">
            <!-- notice teaser no image -->
            <article class="business-list-without-thumb-stories" data-uuid="'.$uuid.'">
              <div class="post-title-rows">
                <div class="heading">
                  <h4>
                    <a href="'.$permalink.'">'.$title.'</a>
                  </h4>
                </div>
              </div>
            </article>
          </div>';
        }

        if( $key == 5 ){
          $html .= '</div><div class="col-12 col-lg-6 board-col-xs-6 board-col-sm-6 board-col-md-5 board-col-lg-5 biz-lt-no-thmb-cln-snd">';
        }

        if( $key == 7 ){
          $html .= '</div>';
        }
        
      }

      if( $main_section == 'entertainment' ){
        if( $key == 0 ){
          $html .= '<div class="col-12 col-lg-3 board-col-xs-12 board-col-sm-12 board-col-md-2 board-col-lg-2 homepageentleft">
            <div class="rendered_board_article content-size-xs-12 content-size-sm-12 content-size-md-2 content-size-lg-2">
              <!-- 2024 notice teaser with image -->
              <div class="col-lg-12 col-md-12 col-12" data-uuid="'.$uuid.'">
                <div class="post-title-rows">
                  <div class="thumb-with-image image-zoom">
                    <a href="'.$permalink.'">
                       <img class="img-fluid lazyload" data-srcset="'.$imagelink2.'" srcset="'.$imagelink2.'" src="'.$imagelink2.'" onerror="console.log(this.srcset); this.srcset = https://image.khaleejtimes.com/?uuid=6b5e4369-15ca-52b5-a3cd-432ee563d856&function=original&type=preview; console.log(this.srcset);"   style="width: 100%; height: auto;" alt="'.$title.'">
                    </a>
                  </div>
                  <h4>
                    <a href="'.$permalink.'">'.$title.'</a>
                  </h4>
                  <p>
                    <a href="'.$permalink.'">'.$teaserbody.'</a>
                  </p>
                </div>
              </div>
            </div>
          </div>';
        }

        if( $key == 1 ){
          $html .= '<div class="col-6 col-lg-3 board-col-xs-6 board-col-sm-6 board-col-md-2 board-col-lg-2 entertainment hme-entertaimt-mb-fst-clmn">';
        }

        if( $key == 3 ){
          $html .= '<div class="col-6 col-lg-3 board-col-xs-6 board-col-sm-6 board-col-md-2 board-col-lg-2 entertainment hme-entertaimt-mb-snd-clmn">';
        }

        if( $key == 5 ){
          $html .= '<div class="col-6 col-lg-3 board-col-xs-6 board-col-sm-6 board-col-md-2 board-col-lg-2 entertainment hme-entertaimt-last-clmn">';
        }

        if( $key > 0){
          $html .= '<div class="rendered_board_article content-size-xs-6 content-size-sm-6 content-size-md-2 content-size-lg-2">
            <!-- 2024 notice teaser with image -->
            <div class="col-lg-12 col-md-12 col-12" data-uuid="'.$uuid.'">
              <div class="post-title-rows">
                <div class="thumb-with-image image-zoom">
                  <a href="'.$permalink.'">
                    <img class="img-fluid lazyload" data-srcset="'.$imagelink.'" srcset="'.$imagelink.'" src="'.$imagelink.'" onerror="console.log(this.srcset); this.srcset = https://image.khaleejtimes.com/?uuid=6b5e4369-15ca-52b5-a3cd-432ee563d856&function=original&type=preview; console.log(this.srcset);"  style="width: 100%; height: auto;" alt="'.$title.'">
                  </a>
                </div>
                <h4>
                  <a href="'.$permalink.'">'.$title.'</a>
                </h4>
              </div>
            </div>
          </div>';
        }

        if( $key == 2 || $key == 4 || $key == 6 ){
          $html .= '</div>';
        }

      }
      
    }

    if( $main_section == 'uae' ){
      $html .= '</div>
          </div>
        </section>
      </div>
      </div>';
    }

    if( $main_section == 'world' ){
      $html .= '</div>';
    }

    if( $main_section == 'life-living' || $main_section == 'sports' || $main_section == 'lifestyle' ){
      $html .= '</div>';
    }
    
    if( $main_section == 'business' ){
      $html .= '</section>
          </div>
        </div>';
    }
  }
  echo $html; die;
}

function get_permalinks_sections(string $uuid): ?string
{

  $articles = get_posts([
    'post_type' => 'Article',
    'meta_key' => 'oc_uuid',
    'meta_value' => $uuid
  ]);
  $article = $articles[0] ?? null;
  $permalink=$article ?get_permalink($article->ID): null;
  return $permalink;
}


function get_section_listing(){
  $limit = 10;
  if( !empty( $_POST['limit'] ) && is_numeric( $_POST['limit'] ) ){
    $limit  = $_POST['limit'];
  }

  $pagenr = 1;
  if( !empty( $_POST['pagenr'] ) ){
    $pagenr = $_POST['pagenr'];
  }
  
  $start = ($pagenr * $limit) - $limit;

$sectiontext="";
  
  if(@$_POST['section']!='notapplicable')
  {
    $section = $_POST['section'];
    $sectiontext="&Section={$section}";
  }
  $sub_section = $_POST['sub_section'];
  $data = array();
  $username = 'ktd';
  $password = 'ycvyxveQyR72xaENKK@QqBcB';

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://xlibris.public.prod.oc.ktd.infomaker.io:8443/opencontent/search?limit={$limit}&start={$start}&q='{$sub_section}'&Status=usable&sort.Pubdate.ascending=false&sort.indexfield=Pubdate&contenttype=Article$sectiontext",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_USERPWD => $username . ":" . $password,
  ));

  $responseOneContent = curl_exec($curl);
  curl_close($curl);

  $responseOneContent = json_decode( $responseOneContent, true );
  $html = '';
  if( isset( $responseOneContent['hits']['hits'] ) ){
    $articles = $responseOneContent['hits']['hits'];

    foreach ($articles as $key => $article) {
      $articleData = array_shift( $article['versions'] );

      $articleData = $articleData['properties'];

      $uuid        = array_shift( $articleData['uuid'] );
      $title       = array_shift( $articleData['Headline'] );
      $permalink   = get_permalinks_sections( $uuid );
      $sectionName = array_shift( $articleData['Section'] );
      $teaserbody  = $articleData['TeaserBody'][0];
      $imageUUID   = array_shift( $articleData['TeaserImageUuids'] );
      if(!$imageUUID):      
        $imagelink="https://image.khaleejtimes.com/?uuid=6b5e4369-15ca-52b5-a3cd-432ee563d856&function=original&type=preview";      
        else:
      $image=get_image_type("https://image.khaleejtimes.com/?uuid=$imageUUID&function=original&type=preview",450);
      $imagelink="https://image.khaleejtimes.com/?uuid=$imageUUID&function=cropresize&type=preview&source=false&q=75&crop_w=0.77125&crop_h=0.91556&$image&x=0.1375&y=0.02222/";
        endif;
      $html .= '<article class="listing-normal-teasers card-article-list-item" style="display: block;" data-uuid="'.$uuid.'">
      <div class="row">
        <div class="col-lg-3 col-md-3 col-4">
        <div class="post-title-rows">
          <div class="thumb-with-image image-zoom"> 
          <a href="'.$permalink.'">
          <img class="img-fluid" data-srcset="'.$imagelink.'" srcset="'.$imagelink.'" src="'.$imagelink.'" onerror="console.log(this.srcset); this.srcset = https://image.khaleejtimes.com/?uuid=6b5e4369-15ca-52b5-a3cd-432ee563d856&function=original&type=preview; console.log(this.srcset);" style="width: 100%; height: auto;" alt="'.$title.'">
          </a> 
          </div>
        </div>
        </div>
        <div class="col-lg-9 col-md-9 col-8">
        <h3><a href="'.$permalink.'">'.$title.'</a></h3>
        <p><a href="'.$permalink.'">'.strip_tags( $teaserbody ).'</a></p>
        </div>
      </div>
      </article>';
    }
  }
  echo $html; die;
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