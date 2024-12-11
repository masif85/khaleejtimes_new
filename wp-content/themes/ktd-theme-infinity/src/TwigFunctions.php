<?php
declare(strict_types=1);

namespace KTDTheme;

use Everyware\Plugin\SettingsParameters\SettingsParameter;
use Infomaker\Everyware\Base\Sidebars;
use Infomaker\Everyware\Base\Utilities;
use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use DOMDocument;
/**
 * TwigFunctions
 *
 * @package Customer\Utilities
 */
 
	
class TwigFunctions
{

function __construct(){
        
        $this->token_lullu="https://orvillestaging.luluone.com:8443/hbapi/v1_0/auth/token";
        $this->auth_lullu="https://orvillestaging.luluone.com:8443/hbapi/v1_0/pas/";
        $this->agent_code="784278";
        $this->lullu_username="gdnadmin";
        $this->lullu_password="KhfYqJhb05q9BVjJfyIArjW";
        $this->lullu_secret="KFaMnJzHWa2CgER87PkpruzyXSH9oEDE1LjhJUDu";
    } 

  public function inProduction(): bool
  {
    return is_prod();
  }

  public function renderClasses($classes): string
  {
    return implode(' ', array_filter((array)$classes));
  }

public function get_lulu_token()
{
		$curl = curl_init();
		curl_setopt_array($curl, array(
	  CURLOPT_URL => $this->token_lullu,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => false,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'POST',
	  CURLOPT_POSTFIELDS =>'{
	    "username": "'.$this->lullu_username.'",
	    "password": "'.$this->lullu_password.'"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'domain: tenants',
    'clientid: gdn',
    'clientsecret: '.$this->lullu_secret
  ),
));
$response = curl_exec($curl);
curl_close($curl);
return json_decode($response);
}

public function get_lullu_rates($name="")
{
$token=$this->get_lulu_token();
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => $this->auth_lullu.$name,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => false,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>' {
	"agentcode": '.$this->agent_code.'
 }
',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'sender: gdn',
    'company: 784100',
    'Authorization: Bearer '.$token->token->access_token
  ),
));
$response = curl_exec($curl);
curl_close($curl);
$data=json_decode($response);
//echo "<pre>";
//print_r($data->data->rates);
return @$data->data->rates;
}

  public function renderPrayertimes(string $template, string $uuid): void
  {
    $provider = OpenContentProvider::setup([
      'uuid' => "$uuid",
      'contenttype' => 'Article'
    ]);
    $provider->setPropertyMap('Article');
    $articles = $provider->queryWithRequirements();

    if(count($articles) > 0) {
      if ($articles[0]['body_raw']) {
        $xml  = simplexml_load_string($articles[0]['body_raw']);
        $json = json_encode($xml->group->children());
        $array = json_decode($json,TRUE);
      }
    }
      
    View::render( $template, [
      'content' => $array
    ] );
  }

	public function get_video_id($str)
	{
	//preg_match_all("/data-video=\"(\d+)\"/", $str, $matches);
	preg_match_all('/data-video="([^"]*)"/', $str, $matches );
	if(isset($matches[1][1])){
		return($matches[1][1]);	
		}
		else
		{
			return 0;
		}
	}

	public function get_vertical($str)
	{
		preg_match_all('/src="([^"]*)"/', $str, $matches );
		if(count($matches[0])>0)
		{
		$nmatch= $matches[0][0];
		if (strpos($nmatch, "youtube") === false) {
		$nmatch=explode("/",$nmatch);		
		return $nmatch[4];	
		}
		else
		{
			return "youtube";
		}
	}else
	{
		return false;
	}
				
	}

  public function renderPartial(string $template, array $data = []): void
  {
    View::render($template, $data);
  }

  public function settingsParameter(string $key): ?string
  {
    return SettingsParameter::getValue($key);
  }

  public function renderSidebar(string $id): void
  {
    Sidebars::render($id);
  }
public function get_url_param($param="")
{
	return @$_GET["$param"];
}
  public function getTemplateDirUri(bool $childTheme = false): string
  {
    return Utilities::getCdnUri();
  }

  public function getConceptLink(string $uuid): ?string
  {
    $concepts = get_posts([
      'post_type' => 'concept',
      'meta_key' => 'oc_uuid',
      'meta_value' => $uuid
    ]);
    
    $concept = $concepts[0] ?? null;

    return $concept ? wp_make_link_relative(get_permalink($concept->ID)) : null;
  }

  public function strBetween(string $string, string $start, string $end): ?string
  {
    preg_match(sprintf('#\%s(.*?)\%s#', $start, $end), $string, $match);

    return $match[1];
  }
	

	  public function get_part($part,$body)
	{
		$data=$this->wildcard_in_array("ew-embed article__iframe",$body);	
		if ($data)
		{
		$data = array_values($data);
		$html=$data[0];		
		$dom = new DOMDocument();
		$dom->loadHTML($html);
		$metas = $dom->getElementsByTagName('meta');
		foreach($metas as $meta) {
    if($meta->getattribute('itemprop') == "$part") {
        return $meta->getattribute('content');
    		}
			}
		}
		else if($this->wildcard_in_array("entry-content movie-preview",$body) && $part=="src")
		{
		$data=$this->wildcard_in_array("entry-content movie-preview",$body);
		if ($data)
		{
		$data = array_values($data);
		$html=$data[0];
		$dom = new DOMDocument();
		$dom->loadHTML($html);
		$metas = $dom->getElementsByTagName('iframe');
		foreach($metas as $meta) {			
    if(strpos($meta->getattribute('src'),'youtube') !== false) {
        return $meta->getattribute('src');
       
    		}
			}		
		
		}

		}
		else
		{
			return false;
		}
	
	}

public function wildcard_in_array($string, $array = array ())
{       
    foreach ($array as $key => $value) {
        unset ($array[$key]);
        if (strpos($value, $string) !== false) {
            $array[$key] = $value;
        }
    }       
    return $array;
}


public function get_list_viral()
{
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://xlibris.public.prod.oc.ktd.infomaker.io:8443/opencontent/search?limit=1&sort.name=created&contenttype=List&Name=Going%20Viral',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => false,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Basic a3RkOnljdnl4dmVReVI3MnhhRU5LS0BRcUJjQg=='
  ),
));

$response = curl_exec($curl);
curl_close($curl);
$articles=json_decode($response);
$top_article=$articles->hits->hits['0']->versions['0']->properties->ArticleUuids;
$art_list="";
foreach($top_article as $art)
{
	$art_list.=$this->getuart($art);
}
return $art_list;
}

public function getuart(string $uuid): ?string
  {
    $articles = get_posts([
      'post_type' => 'Article',
      'meta_key' => 'oc_uuid',
      'meta_value' => $uuid
    ]);
    
    $article = $articles[0] ?? null;

    $permalink=$article ?get_permalink($article->ID): null;
    $title=$article ?get_the_title($article->ID): null;

    $datalink='<li class="ew-article-list__item">
              <article>
                <div class="entry-item">
                  <h4><a href="'.$permalink.'" class="color-underline stretched-link">'.$title.'</a></h4>
                </div>
              </article>
            </li>';


    return $datalink;
  }


public function search_string_array($owned_urls,$string)	
	{
		foreach ($owned_urls as $url) {    
			if (strpos($string, $url) !== FALSE) { 
				return true;
			}
		}
		return false;		
	}
	
		public function get_url_part($type="")
	{
		if($type=='first')
		{
			$base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];
			$url = $base_url;
		}
		
		else if($type=="second")
		{
			
			$url=$_SERVER['REQUEST_URI'];
			
		}
		else if($type=="all")
		{
			$base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];
			$url = $base_url . $_SERVER["REQUEST_URI"];
		}
		else
		{
			$url=$_SERVER['REQUEST_URI'];
		}
		
		
		return $url;
	}
	
public function search_url_part($string="")
{
	$base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];
	$url = $base_url . $_SERVER["REQUEST_URI"];
	$uri_parts = explode('?', $url, 2);
	$parts = explode('/', $uri_parts[0]);	
	if(in_array($string,$parts))
	{
		return true;
	}	
	else
	{
		return false;
	}
}
		
	
	
	
public function get_iframe_src($iframe_string,$match)
	{
		preg_match('/src="([^"]+)"/', $iframe_string, $match);		
		$url = trim($match[1]);
		return $url;
	}
	
public function replace_regex($astring,$pattern)
	{
	return preg_replace('/.*dailymotion.com\/embed\/video\/(.*?)\?.*/','$1', $astring);
	}
	
public function get_url()
	{
		 $base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];
		$url = $base_url . $_SERVER["REQUEST_URI"];		
		return $url;
	}
	
	
	public function get_post($post_id="")
	{
		return get_post_field('post_content', $post_id);

	}	
	

	
public function clean($string) {
		$string =str_replace('"', "", $string);
		$string =str_replace('“', "", $string);
		$string =str_replace('”', "", $string);
		$string =str_replace("/", "", $string);
		$string =str_replace("\\", "", $string);
   //$string = str_replace(' ', ' ', $string); // Replaces all spaces with hyphens.

   return $string;//ltrim(preg_replace('/[^A-Za-z0-9\-:]/', ' ', $string)); // Removes special chars.
}	
	
public function createsection_asf($uri,$sectionName)
    {
        $resolved = [];
        $sections = explode('/', $uri);
		$sections =array_filter( $sections);
		$total=count($sections);
		$count=0;
   foreach ($sections as $section)
   {
	   $section=trim($section);
	   if ($section!="" && ($count!=0 && $count!=1 && $count!=($total-1)))
	   {
		 $resolved[]=$section;  
	   }
	   $count++;
   }
		
		$totalnew=count($resolved);
		
			if($totalnew==3)
			{
				$resolved=$resolved[2];
			}
			else if($totalnew==2)
			{
					$resolved=$resolved[1];
			}
			else
			{
				$resolved=$resolved[0];
			}
		
		if($resolved=='')
		{
			$resolved=$sectionName;
		}
		$resolved=str_replace("-", " ", $resolved);
        return $resolved;
    }	
	
	
	public function get_dailymotion_src($url)
	{
		
 	$array = array();
	$matches=array();
    preg_match( '/src="([^"]*)"/i', $url, $array ) ;
	preg_match('/^.+dailymotion.com\/(?:video|swf\/video|embed\/video|hub|swf)\/([^&?]+)/',$array[1],$matches);
	if($matches[1])
	{
	return $matches[1];
	}
	else
		{
			$id = strtok(basename($array[1]), '&');
			$video_id = substr($id , strpos($id , "=") + 1);    
			return $video_id;
		}

}
	
		public function get_symbol($symbol="")
	{
		$contents = file_get_contents("https://s.tradingview.com/embed-widget/symbol-profile/?symbol=$symbol");
		$contents = strip_tags($contents, "<div>");
 		preg_match('/<div class="tv-error-card__message">(.*?)<\/div>/s', $contents, $match);
		if(@$match[1])
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
}
