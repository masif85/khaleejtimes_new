<?php

/**
* Template Name: RSS Feeds
*/


use USKit\Base\ViewModels\BasePage;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Twig\View;
use USKit\Base\ViewModels\Teaser;
use USKit\Base\ViewModels\BaseObject;
use USKit\Base\Parts\Concept;
use USKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;

header('Content-Type: application/rss+xml; charset=utf-8');

$site_name = get_bloginfo( 'name' );
$site_description = get_bloginfo( 'description' );
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$current_domain = $protocol . $_SERVER['HTTP_HOST'];

if (isset($_GET['categoryId'])) {
	$categoryFilter = $_GET['categoryId'];
} else {
	$categoryFilter = NULL;
}

if (isset($_GET['pageType'])) {
	$pageType = $_GET['pageType'];
} else {
	$pageType = 'story';
}

if (isset($_GET['count'])) {
	$count = $_GET['count'];
} else {
	$count = 10;
}

$rssCategoryName = OpenContentProvider::setup( [
	'q'  => QueryBuilder::where('uuid', $categoryFilter)->buildQueryString(),
] );

$rssCategoryName->setPropertyMap( 'Concept' );

$rssCategoryData = array_map( function ( Concept $concept ) {
    $concept_teaser = new BaseObject( $concept );
	return $concept_teaser->getViewData();
}, $rssCategoryName->queryWithRequirements() );

foreach($rssCategoryData as $category) {
	$categoryName = $category['name'];
}

if ($pageType == 'gallery' ) {
	$rssArticles = OpenContentProvider::setup( [
		'q'						 => QueryBuilder::where('ConceptCategoryUuids', $categoryFilter )->andIfProperty('SubType', 'gallery' )->buildQueryString(),
		'contenttypes'           => [ 'Article' ],
		'sort.indexfield'        => 'Pubdate',
		'sort.Pubdate.ascending'  => true,
		'limit'                  => $count,
		'Status'				 => 'usable'
	] );
} elseif ($pageType == 'video') {
	$rssArticles = OpenContentProvider::setup( [
		'q'						 => QueryBuilder::where('ConceptCategoryUuids', $categoryFilter )->andIfProperty('SubType', 'video' )->buildQueryString(),
		'contenttypes'           => [ 'Article' ],
		'sort.indexfield'        => 'Pubdate',
		'sort.Pubdate.ascending'  => true,
		'limit'                  => $count,
		'Status'				 => 'usable'
	] );	
} else {
	$rssArticles = OpenContentProvider::setup( [
		'q'						 => QueryBuilder::where('ConceptCategoryUuids', $categoryFilter )->andIfNotProperty('SubType', 'video' )->andIfNotProperty('SubType', 'gallery' )->buildQueryString(),
		'contenttypes'           => [ 'Article' ],
		'sort.indexfield'        => 'Pubdate',
		'sort.Pubdate.ascending'  => true,
		'limit'                  => $count,
		'Status'				 => 'usable'
	] );
}

$rssArticles->setPropertyMap( 'Article' );

$rssArticlesData = array_map( function ( NewsMLArticle $article ) {
    $teaser = new Teaser( $article );
	return $teaser->getViewData();
}, $rssArticles->queryWithRequirements() );


$counter = 0;
$rss_images = [];
foreach ($rssArticlesData as $rss) {
		libxml_use_internal_errors(true);

		$bodyraw = $rss['bodyraw'];
		$bodyraw = preg_replace('/\{\"(.+?)\"\}/', '', $bodyraw);
		$bodyraw = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $bodyraw);
		/* ADD CDATA TO ENSURE HTML EMBED IS VALID FOR XML */
		$bodyraw = preg_replace('/\<object id\=\"htmlembed-(.+?)\" type\=\"x-im\/htmlembed\"><data><text>(.*)\<\/text\>/', '<object id="htmlembed-$1" type="x-im/htmlembed"><data><text><![CDATA[$2]]></text>', $bodyraw);
		$xml=simplexml_load_string($bodyraw);

		if ($xml) {
			if ($pageType == 'story' || $pageType == 'video' )  {
				foreach($xml->group->object as $image) {
					if ($image->attributes()->type == 'x-im/image') {
							if ($image->links->link && $image->links->link->data->text) {
								$uuid = (string) $image->links->link->attributes()->uuid;
								$caption = (string) $image->links->link->data->text->asXML();
								$caption = strip_tags($caption);
								${"rss_images_$counter"}[] = array('uuid' => $uuid, 'caption' => $caption);
							}
					}
				};

				foreach($xml->group->object as $htmlembed) {
					if ($htmlembed->attributes()->type == 'x-im/htmlembed') {
						if ($htmlembed->data->text) {
							$embed = (string) $htmlembed->data->text->asXML();
							${"rss_htmlembed_$counter"}[] = array('htmlembed' => $embed);
						}
					}
				};

				foreach($xml->group->object as $youtube) {
					if ($youtube->links->link) {
						$videolink = (string) $youtube->links->link->attributes()->url;
						if ($videolink) {
							${"video_link_$counter"}[] = array('url' => $videolink);
						}
					}
				};	
			}

			if ($pageType == 'gallery') {
				foreach($xml->group->object as $gallery) {
					if ($gallery->attributes()->type == 'x-im/imagegallery') {

						$uuidCounter = 0;
						$rss_gallery_images = [];

						foreach($gallery->links->link as $links) {
							if ($links->attributes()->uuid && $links->data->text) {
								$uuid = (string) $links->attributes()->uuid;
								$caption = (string) $links->data->text->asXML();
								$caption = strip_tags($caption);
								$rss_gallery_images[$uuidCounter] = array('uuid' => $uuid, 'caption' => $caption);
							}
							$uuidCounter++;	
						}
					}
				};
			}

			$rssArticlesData[$counter]['pageType'] = $pageType;

			if ($pageType == 'video' || $pageType == 'story') { 
				if (isset(${"rss_images_$counter"})) {
					$rssArticlesData[$counter]['rss_image_data'] = ${"rss_images_$counter"};
				}
				if (isset(${"rss_htmlembed_$counter"})) {
					$rssArticlesData[$counter]['rss_htmlembed_data'] = ${"rss_htmlembed_$counter"};
				}

				if (isset(${"video_link_$counter"})) {
					$rssArticlesData[$counter]['videoData'] = ${"video_link_$counter"}; 
				} 
			};


			if ($pageType == 'gallery') { 
				$rssArticlesData[$counter]['rss_gallery_image_data'] = $rss_gallery_images; 
			};
			
		}
	
	$counter++;
}

$image_endpoint_url = get_theme_mod('image_endpoint_url' );
if(count($rssArticlesData) > 0) {
	$articles = View::generate( '@base/feeds/feed-articles.twig', [
		'articles' => $rssArticlesData,
		'image_endpoint_url' => $image_endpoint_url
	]);
}

View::render( '@base/feeds/rss-feed.twig', [
	'articles' => $articles,
	'categoryName' => $categoryName,
	'site_name' => $site_name,
	'site_description' => $site_description,
	'current_domain' => $current_domain
] );