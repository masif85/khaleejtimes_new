<?php

/**
 * Template Name: Custom Feeds
 */

use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Twig\View;
use Tightenco\Collect\Support\Arr;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;

// set header
$types=$_GET['content'];
if($types!='ktreceptiontickertext')
{
header('Content-Type: application/xml; charset=utf-8');
}
// get current domain, url and imengine url
$protocol           = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$current_domain     = $protocol . $_SERVER['HTTP_HOST'];
$current_url        = "$_SERVER[REQUEST_URI]";

$options            = get_option( 'oc_options' );
$image_endpoint_url = $options['imengine_url'];
$template           = "";

// get parameters, content is used for feedtype, list for oc list, type for articles, galleries or video
$feedtype      = Arr::get($_GET, 'content',  '0');
$list          = Arr::get($_GET, 'list',    '');
$section       = Arr::get($_GET, 'section', '*');
$limit         = Arr::get($_GET, 'limit',   '20');
$subtype       = Arr::get($_GET, 'type',   'ARTICLE');
$conceptuuid   = Arr::get($_GET, 'cid',   '*');
$channel       = Arr::get($_GET, 'channel', '*');

if ( ! \is_numeric($limit)) {
    $limit = 20;
}
if ($channel != '*') {
    $channel = '"'.$channel.'"';
}

// diffrent template loaded based on parameter type or content
if ($subtype == 'GALLERY') {
    $template = "gallery-";
}
if ($subtype == 'VIDEO') {
    $template = "video-";
}
if ($feedtype == 'google') {
    $template = "google-";
}
if ($feedtype == 'til') {
    $template = "til-";
}
if ($feedtype == 'msn') {
    $template = "msn-";
}
if ($feedtype == 'flipboard') {
    $template = "flipboard-";
}
if ($feedtype == 'covid19') {
    $template = "covid19-";
}
if ($feedtype == 'amplify') {
    $template = "amplify-";
}
if ($feedtype == 'alexa') {
    $template = "alexa-";
}
if ($feedtype == 'facebook') {
    $template = "facebook-";
}
if ($feedtype == 'syndigate') {
    $template = "syndigate-";
}

if ($feedtype == 'dailyhunt') {
    $template = "dailyhunt-";
}

if ($feedtype == 'ktreceptiontickertext') {
    $template = "ktreceptiontickertext-";
}
if ($feedtype == 'ktreceptiontickerrss') {
    $template = "ktreceptiontickerrss-";
}




if ($feedtype == 'sport') {
    $template = "sport-";
    $section  = "Sports";
    if ($conceptuuid != '*') {
        $section  = "*";
        if ($subtype == 'GALLERY') $template = "gallery-";
        if ($subtype == 'VIDEO') $template = "video-";

    }
}

$feedcat = "";
$feedcat = getfeedcat("section",$section);

if($list != "")
    $feedcat = getfeedcat("list",$list);


// this part is for OC Lists
if ($list != '') {
    $ArticlesFromList = GetUuidFromOCList($list,$limit);

    // OC request based on the articles uuids pulled from the OC list
    $provider = OpenContentProvider::setup( [
        'uuid' => "($ArticlesFromList)",
        'contenttype' => 'Article',
        'CustomerContentSubType' => $subtype,
        'limit' => $limit,
        'start' => 0,
        'properties' => ['CustomerVideoId'],
        'Status' => 'usable'
    ] );
}
// if it is not a list
else {
    // OC request to get latest content based on the section
    $provider = OpenContentProvider::setup( [
        'Section' => $section,
        'contenttype' => 'Article',
        'CustomerContentSubType' => $subtype,
        'ConceptUuids' => $conceptuuid,
        'Channels' => $channel,
        'sort.indexfield'        => 'Pubdate',
        'sort.Pubdate.ascending' => true,
        'limit' => $limit,
        'start' => 0,
        'properties' => ['CustomerVideoId'],
        'Status' => 'usable'
        //'properties' => ['VideoId']
    ] );
}

$provider->setPropertyMap('Article');
$articles     = $provider->queryWithRequirements();

//Content containers only display Articles
if ( is_array( $articles ) ) {
    add_filter( 'ew_content_container_fill', function ( $arr ) use ( $articles ) {
        $arr = array_merge( $arr, $articles );

        return $arr;
    } );
}

//parse body on all articles
$articles = BodyParser($articles, $subtype);


// rearange the articles, so it has the same sorting as OC List
if ($list != '') {
    // overwrite the articles array with the new sorted array
    $articles = RearrangeArticles($ArticlesFromList, $articles);

}

// if there is content, genereate each content
if (count($articles) > 0) {
    $articles = View::generate( '@base/feeds/'.$template.'item.twig', [
        'articles' => $articles,
        'image_endpoint_url' => $image_endpoint_url,
        'subtype' => $subtype,
    ]);
}

if ( is_array( $articles ) ) {
    $articles = '';
}

// render the final template
View::render( '@base/feeds/'.$template.'items.twig', [
    'articles' => $articles,
    'section' => $section,
    'feedcat' => $feedcat,
	"template"=>$template,
    'current_domain' => $current_domain,
    'subtype' => $subtype,
    'current_url' => $current_url
] );

function GetUuidFromOCList($PList, $PLimit) {
    $ArticlesFromList = "";
    $ListCounter = 0;

    // OC request, to get articles from the OC List
    $query = QueryBuilder::where('uuid', $PList);
    $ListOc = OpenContentProvider::setup( [
        'q'  => $query->buildQueryString(),
        'contenttypes' => ['List'],
        'properties' => ['ArticleUuids']
    ] );

    $ListOc->setPropertyMap('List');
    $list = $ListOc->queryWithRequirements();


    // go trough the result, to get the article uuids for later OC request
    if ($list ) {
        foreach ($list[0]['articles'] as $item) {

            $ArticlesFromList.=  $item['uuid'] . " OR ";
            $ListCounter++;
            if ($ListCounter == $PLimit) break;
        }
        $ArticlesFromList.= "END";
        $ArticlesFromList = str_replace(" OR END","",$ArticlesFromList);
    }
    return $ArticlesFromList;
}
function BodyParser($PArticles, $PSubType) {

    $counter     = 0;
    $ImageCounter = 0;
    $bodyraw     = '';

    // loop trough each article
    foreach ($PArticles as $item) {
        $ImageCounter                 = 0;
        $bodyraw                     = $item['body_raw'];
        $PArticles[$counter]['body'] = '';
        $xml                         = simplexml_load_string($bodyraw);
        unset($images);

        if ($xml) {

            // go trough each node in the body
            foreach ($xml->group->children() as $element) {
                // get the type of the node
                $type = $element->attributes()->type;

                if ($type == 'preamble') {
                    $PArticles[$counter]['summary'] .= $element;
                }

                if ($type == 'body') {
                    $PArticles[$counter]['body'] .= "<p>$element</p>";
                }

                // take only the first image in the body
                if ($type == 'x-im/image' AND $ImageCounter == 0) {
                    if ($element->links->link ) {
                        $uuid = (string) $element->links->link->attributes()->uuid;
                        $caption = "";
                        if ($element->links->link->data->text ) {
                            $caption = (string) $element->links->link->data->text->asXML();
                            $credit = '';
                            if ($element->links->link->links ) {
                                $i = 0;
                                foreach($element->links->link->links->link as $imgauthor) {
                                    $credit  .= (string) $imgauthor->attributes()->title;
                                    if(++$i != count($element->links->link->links->link)) {
                                        $credit  .= ", ";
                                    }
                                }
                            }
                            $caption = strip_tags($caption);
                        }
                        ${"images"}[] = array('uuid' => $uuid, 'caption' => $caption, 'author' => $credit);
                    }
                    if ($PSubType != 'GALLERY')
                        $ImageCounter++;
                }

                if ($type == 'x-im/htmlembed') {
                    if ($element->data->text) {
                        $embed = (string) $element->data->text->asXML();
                        $PArticles[$counter]['body'] .= $embed;
                    }

                }

                if ($type == 'x-im/table') {
                    $PArticles[$counter]['body'] .= "<table>".(string)$element->children()->asXML()."</table>";
                }

                if ($type == 'x-im/youtube') {
                    if ($element->links->link) {
                        $videolink = (string) $element->links->link->attributes()->url;
                        if ($videolink) {
                            $PArticles[$counter]['body'] .= "<blockquote><iframe width=\"560\" height=\"315\" src=\"$videolink\" frameborder=\"0\" allow=\"accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen> </iframe></blockquote>";
                        }
                    }
                }


                if ($type == 'x-im/unordered-list' OR $type == 'x-im/ordered-list') {
                    $ListItem = str_replace("list-item","li",(string)$element->children()->asXML());
                    if ($type == 'x-im/ordered-list'){
                        $ListItem = "<ol>$element</ol>";
                    }
                    else {
                        $ListItem = "<ul>$element</ul>";
                    }

                    $PArticles[$counter]['body'] .= $ListItem;
                }

                if ($type == 'x-im/socialembed') {
                    if ($element->links->link) {
                        $SocialType = (string) $element->links->link->attributes()->type;
                        $SocialLink = (string) $element->links->link->attributes()->url;
                        $SocialBody = "";
                        if ($SocialType) {
                            if ($SocialType == "x-im/instagram") {
                                $SocialBody = "<blockquote class=\"instagram-media\" style=\"background: #FFF; border: 0; border-radius: 3px; box-shadow: 0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width: 658px; min-width: 326px; padding: 0; width: calc(100% - 2px);\" data-instgrm-captioned=\"\" data-instgrm-permalink=\"$SocialLink\" data-instgrm-version=\"13\"><div style=\"padding: 16px;\"><div style=\"display: flex; flex-direction: row; align-items: center;\"> </div><div style=\"padding: 19% 0;\"> </div><div style=\"display: block; height: 50px; margin: 0 auto 12px; width: 50px;\"> </div><div style=\"padding-top: 8px;\"><div style=\"color: #3897f0; font-family: Arial,sans-serif; font-size: 14px; font-style: normal; font-weight: 550; line-height: 18px;\">View this post on Instagram</div></div></div></blockquote><script src=\"https://platform.instagram.com/en_US/embeds.js\" async=\"\"></script>";

                            }
                            if ($SocialType == "x-im/tweet") {
                                $SocialBody = "<blockquote class=\"twitter-tweet\"><a href=\"$SocialLink\"></a></blockquote><script src=\"https://platform.twitter.com/widgets.js\" async=\"\" charset=\"utf-8\"></script>";

                            }
                            if ($SocialType == "x-im/soundcloud") {

                            }
                            $PArticles[$counter]['body'] .= $SocialBody;

                        }
                    }
                }
                // if pulling galleries, generate a new variable gallery_image
                if ($PSubType == 'GALLERY') {
                    if ($type == 'x-im/imagegallery') {
                        $uuidCounter = 0;
                        $gallery_images = [];

                        foreach($element->links->link as $links) {
                            if ($links->attributes()->uuid && $links->data->text) {
                                $uuid = (string) $links->attributes()->uuid;
                                $caption = (string) $links->data->text->asXML();
                                $caption = strip_tags($caption);
                                $gallery_images[$uuidCounter] = array('uuid' => $uuid, 'caption' => $caption);
                            }
                            $uuidCounter++;
                        }
                        $PArticles[$counter]['gallery_image'] = $gallery_images;
                    }

                }
            }
            if (isset(${"images"})) {
                $PArticles[$counter]['images'] = ${"images"};
            }
        }
        $counter++;
    }
    return $PArticles;
}
function RearrangeArticles($PArticlesFromList, $PArticles) {

    // convert the OR query part to an array, this is the correct order
    $PArticlesFromList    = str_replace(" OR ",",",$PArticlesFromList);
    $ArrArticlesFromList = explode(",", $PArticlesFromList);

    $ListOrderArticles = array();
    $ListCounter       = 0;

    // loop trough each uuid from the correct list order, and if there is a match, place it in the array
    foreach ($ArrArticlesFromList as $ArticleUuid) {
        foreach ($PArticles as $temparticle) {
            if ($ArticleUuid == $temparticle['uuid']) {
                $ListOrderArticles[$ListCounter] = $temparticle;
            }
        }
        $ListCounter++;
    }
    return $ListOrderArticles;
}
function getfeedcat($Pwhat,$PValue){
    $LCat = "";
    if($Pwhat == "list"){
        if($PValue == "f72411ed-9409-4ac7-aa94-dc8f35d991c8")
            $LCat = "Top News Home";
    }
    else{
        $LCat = $PValue;
    }
    return $LCat;
}
