<?php

/**
 * Template Name: Sponsored Articles Preview
 */

use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Twig\View;
use Tightenco\Collect\Support\Arr;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;


$status        = Arr::get($_GET, 'status',   'preview');
$limit          = Arr::get($_GET, 'limit',   '20');
$type          = Arr::get($_GET, 'type',   '');



// OC request to get latest content based on the section
$provider = OpenContentProvider::setup( [
  'contenttype' => 'Article',
  'sort.indexfield'        => 'updated',
  'sort.updated.ascending' => true,
  'limit' => $limit,
  'start' => 0,
  'Status' => $status
] );


$provider->setPropertyMap('Article');
$articles     = $provider->queryWithRequirements();

//Content containers only display Articles
if ( is_array( $articles ) ) {
  add_filter( 'ew_content_container_fill', function ( $arr ) use ( $articles ) {
    $arr = array_merge( $arr, $articles );

    return $arr;
  } );
}

$text = "Preview Sponsored Articles";
if ( $status == "usable")
  $text = "Lastest Published Articles";

if ( $type == "RSS")
{
  // set header
  header('Content-Type: application/xml; charset=utf-8');
  header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
  header( 'expires:0' );


  echo "
  <rss xmlns:content=\"http://purl.org/rss/1.0/modules/content/\" xmlns:atom=\"http://www.w3.org/2005/Atom\" xmlns:media=\"http://search.yahoo.com/mrss/\" version=\"2.0\">
    <channel>
        <title><![CDATA[Khaleej Times]]></title>
        <link>http://www.khaleejtimes.com</link>
        <description><![CDATA[".$text."]]></description>
        <language>en-gb</language>";

  foreach ($articles as $item) {
    echo "
          <item>
            <title><![CDATA[". $item['headline'] ."]]></title>
            <pubDate>".date("r", strtotime($item['objectUpdated']))."</pubDate>
            <description>". str_replace("&", "&amp;", $item['permalink']) ."</description>
            <link>". str_replace("&", "&amp;", $item['permalink']) ."</link>
          </item>
     ";
  }


  echo "
    </channel>
  </rss>";
}
else{
  foreach ($articles as $item) {
     echo date("r", strtotime($item['objectUpdated'])) . ' -- ' . $item['headline'] . ' -- ' . '<a target="_blank" href="'. $item['permalink'] .'">link</a> <br><br>';
  }
}

