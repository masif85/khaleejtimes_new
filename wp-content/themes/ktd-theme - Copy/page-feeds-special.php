<?php

/**
 * Template Name: Custom Feeds
 */

use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Twig\View;
use Tightenco\Collect\Support\Arr;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;

// set header
header('Content-Type: application/xml; charset=utf-8');

// get current domain, url and imengine url
$protocol           = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$current_domain     = $protocol . $_SERVER['HTTP_HOST'];
$current_url        = "$_SERVER[REQUEST_URI]";

$options            = get_option( 'oc_options' );
$image_endpoint_url = $options['imengine_url'];
$template           = "";
$gf_pt              = array("gold", "silver", "draft", "prayertime","arabicdate");


// get parameters, content is used for feedtype, list for oc list, type for articles, galleries or video
$feedtype      = Arr::get($_GET, 'content',  '0');

$uuids = [
    'gold'      => 'a2344da1-7204-4131-9f2c-1b94a6688cbd',
    'silver'      => '966d14a5-be5b-484b-958c-5cebd45042cb',
    'draft'       => 'b79e4450-81f2-46ec-8752-6c2ff8e37890',
    'prayertime'  => '8cdce398-b4c3-491d-b72e-006b62f5f72a',
    'arabicdate'  => '8cdce398-b4c3-491d-b72e-006b62f5f72a'
];

if (array_key_exists($feedtype, $uuids)) {
    $template = $feedtype . "-";

    $provider = OpenContentProvider::setup( [
        'uuid' => $uuids[$feedtype],
        'contenttype' => 'Article'
    ] );

    $provider->setPropertyMap('Article');
    $articles = $provider->queryWithRequirements();



    if(count($articles) > 0) {
        if ($articles[0]['body_raw']) {
            $xml  = simplexml_load_string($articles[0]['body_raw']);
            $json = json_encode($xml->group->children());
            $array = json_decode($json,TRUE);
        }
    }



    // if there is content, genereate each content
    if (count($articles) > 0) {
        $articles = View::generate( '@base/feeds/'.$template.'item.twig', [
            'content' => $array,
            'image_endpoint_url' => $image_endpoint_url
        ]);
    }

    if ( is_array( $articles ) ) {
        $articles = '';
    }

    // render the final template
    View::render( '@base/feeds/'.$template.'items.twig', [
        'content' => $articles,
        'current_domain' => $current_domain,
        'current_url' => $current_url
    ] );

}






