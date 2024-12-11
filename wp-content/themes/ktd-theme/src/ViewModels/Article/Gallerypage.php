<?php declare (strict_types = 1);

namespace KTDTheme\ViewModels\Article;

use EuKit\Base\ViewModels\RelatedConcept;
use EuKit\Base\Parts\NewsMLArticle;
use EuKit\Base\Parts\Concept;
use Infomaker\Everyware\Base\ArticleBodyParser;
use KTDTheme\ViewModels\Amp\AmpArticlePage;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Base\ThemeMods;
use Infomaker\Everyware\NewsML\Parsers\ImageParser;
use Infomaker\Everyware\NewsML\Parsers\ImageGalleryParser;
use Infomaker\Everyware\Support\Arr;
use Infomaker\Everyware\Support\GenericPropertyObject;
use Infomaker\Everyware\Twig\View;
use KTDTheme\ArticleBodyPresentation;
use KTDTheme\ViewModels\CinemaListingPage;
use KTDTheme\ViewModels\Page;
use KTDTheme\ViewModels\Teasers\Teaser;
use Tightenco\Collect\Support\Collection;
use Infomaker\Everyware\NewsML\Objects\TeaserItem;
use KTDTheme\ViewModels\Image;

class Gallerypage extends Page
{
  protected $teaserRatio = '16:9';

  public function __construct(NewsMLArticle $article)
  {
    $propertyObject = new GenericPropertyObject();
    $propertyObject->fill($article->getProperties());

    parent::__construct($propertyObject);

    $this->default();
    $this->getTeasers($article);
    $this->getBody($article);
    $this->getConcepts($article);
    $this->getOther($article);
   // $this->netcore_articles();
  }

  protected function default(): void
  {

    
    $this->setViewData('settings', ThemeMods::getModsByPrefix('article'));
    $this->setViewData('tealiumGroup', 'article');
    $this->setViewData('adGroup', 'article');
	$this->setViewData('show_floater_mykt', 'false');  
	if (! empty($_GET['utm_source'])) {
      $this->setViewData('show_floater_mykt', $_GET['utm_source']);
    } 
	 
  }

  /**
   * Set article teaser variables
   *
   * @param NewsMLArticle $article
   * @return void
   */
  protected function getTeasers(NewsMLArticle $article): void
  {
    $socialTeaser = $article->getNewsMLSocialTeaser($article);
    $socialTeaser['text'] = strip_tags($socialTeaser['text'] ?? '');

    $this->setViewData('socialTeaser', $socialTeaser);
    $this->setViewData('socialTeaserImage', $this->getTeaserImage($article->getNewsMLSocialTeaser() ?: null));
    $this->setViewData('teaserImage', $this->getTeaserImage($article->getNewsMLTeaser() ?: null));
  }

  /**
   * Set article variables related to body or require parsing of body
   *
   * @param NewsMLArticle $article
   * @return ArticleBodyParser
   */
  protected function getBody(NewsMLArticle $article): ArticleBodyParser
  {
    $articleBody = $article->getParsedBody();
    $articleBodyPresentation = new ArticleBodyPresentation($article);
    $this->setViewData('mainImage', $this->getMainImage($articleBody));
    $this->setViewData('body', $articleBody->generateContent($articleBodyPresentation));
    $this->setViewData('relatedArticles', $articleBody->allOfType('x-im/articlex'));

    return $articleBody;
  }

  /**
   * Set variables related to concepts
   *
   * @param NewsMLArticle $article
   * @return void
   */
  protected function getConcepts(NewsMLArticle $article): void
  {
    $this->setViewData('authors', $this->extractViewData($article->getAuthors()));
    $this->setViewData('tags', $this->extractViewData($article->getTags()));
    $this->setViewData('places', $this->extractViewData($article->getFilteredPlaces()));
    $this->setViewData('categories', $this->extractViewData($article->getCategories()));
    $this->setViewData('channels', $this->extractViewData($article->getChannels()));
    $this->setViewData('stories', $this->extractViewData($article->getStories()));
  }

  /**
   * Get article image from teaser
   *
   * @param TeaserItem $teaser
   * @return array|null
   */
  protected function getTeaserImage(TeaserItem $teaser = null): ?array
  {
    if (!$teaser || !$teaser->getImage()) {
      return null;
    }

    $imageItem = $teaser->getImage();
    $imageItem->set('text', $teaser->title);
    $image = new Image($imageItem);
    $image->setRatio($this->teaserRatio);

    return $image->getViewData();
  }

  /**
   * Add other necessary data to viewdata.
   * Add more articles from same section.
   *
   * @param NewsMLArticle $article
   * @return void
   */

  protected function getOther(NewsMLArticle $article): void
  {	
    $provider = OpenContentProvider::setup([
      'q' => QueryBuilder::where('Section', $article->section)
        ->andIfProperty('CustomerContentSubType', $article->contentsubtype)
        ->andIfNotProperty('uuid', $article->uuid)
        ->buildQueryString(),
      'contenttypes'           => [ 'Article' ],
      'sort.indexfield'        => 'Pubdate',
      'sort.Pubdate.ascending' => 'false',
      'limit'                  => 18
    ]);
    $provider->setPropertyMap('Article');
    $articles = array_map(static function (NewsMLArticle $article) {
      return (new Teaser($article))->getViewData();
    }, $provider->queryWithRequirements());

    $this->setViewData('loadMoreArticles', array_slice($articles, 0, 4));
    //$this->setViewData('infinitearticles', $this->netcore_articles($article->section));
    $this->setViewData('moreFromThisConcept', array_slice($articles, 10, 8));
  }

  /**
   * Map concept data
   *
   * @param Collection $collection
   * @return Collection
   */
  private function extractViewData(Collection $collection): Collection
  {
    return $collection->map(function (Concept $concept) {
      return (new RelatedConcept($concept))->getViewData();
    });
  }

  private function getMainImage(ArticleBodyParser $articleBody): ?array
  {
    if ($teaserImage = $this->getViewData()['teaserImage'] ?? null) {
      return $teaserImage;
    }

    if ($articleBody->hasType(ImageParser::OBJECT_TYPE)) {
      $image = new Image($articleBody->firstOfType(ImageParser::OBJECT_TYPE));
      $image->setRatio($this->teaserRatio);

      return $image->getViewData();
    }

    if ($gallery = $articleBody->firstOfType(ImageGalleryParser::OBJECT_TYPE)) {
      $images = $gallery->getItemAttribute('images');
      if ($images) {
        $image = new Image(reset($images));
        $image->setRatio($this->teaserRatio);

        return $image->getViewData();
      }
    }

    return null;
  }

  /**
   * Create article page from article
   *
   * @param NewsMLArticle $article
   * @return self
   */
  public static function createFrom(NewsMLArticle $article): self
  {
    //print_r($article->permalink);
   // exit;
    //if($article->permalink)

    if ($article->permalink=='https://site.everywarestarterkit.local/mena/daesh-gets-tougher-in-face-of-air-strikes-claims-leader') {
      return  Gallerypage($article);
    }



    if (! empty($_GET['amp'])) {
      return new AmpArticlePage($article);
    }

    if (Arr::get($_GET, 'infinite', '')) {
      return new InfiniteArticlePage($article);
    }

    switch ($article->contentsubtype) {
      case 'SPONSOREDCONTENT':
        return new SponsoredArticlePage($article);
      case CinemaListingPage::SUB_CONTENT_TYPE:
        return new CinemaListingArticlePage($article);
      default:
        return new static($article);
    }
  }

  /**
   * Render article page view
   *
   * @return void
   */
 

   public function netcore_articles($section="")
   {
    /*
     $rand=$_COOKIE["boxx_token_id_kt"];
   
     if(!isset($_COOKIE["boxx_token_id_kt"]))
     {
       $rand="9ff2406c-1aa5-40a9-8233-0927b8de9200";
     }
   $curl = curl_init();
   curl_setopt_array($curl, array(
     CURLOPT_URL => 'https://loki.boxx.ai/',
     CURLOPT_RETURNTRANSFER => true,
     CURLOPT_ENCODING => '',
     CURLOPT_MAXREDIRS => 10,
     CURLOPT_TIMEOUT => 0,
     CURLOPT_FOLLOWLOCATION => false,
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
       "boxx_token_id": "'.$rand.'",      
       "item_filters": {"n_days_old": {"$lt":3},"category":"'.$section.'"},
       "related_products": [],
       "exclude": [],
       "num": 4,
       "get_product_properties": true,
       "get_product_aliases": false
     }
   }',
     CURLOPT_HTTPHEADER => array(
       'Content-Type: application/json',
       'Cookie: AWSALB=RqBmtzSpE7V8TPbDPAXf9FM3h4UPdlzIer8B+2PBxFVNG2zpedSEkGcC8xJRDGmDgCsruywVvu+48/vJNBUgMoJhIa4sG9D8pVfuCno9NfciCZNgdsP/CBXbrsFx; AWSALBCORS=RqBmtzSpE7V8TPbDPAXf9FM3h4UPdlzIer8B+2PBxFVNG2zpedSEkGcC8xJRDGmDgCsruywVvu+48/vJNBUgMoJhIa4sG9D8pVfuCno9NfciCZNgdsP/CBXbrsFx'
     ),
   ));
   $response = curl_exec($curl);
   curl_close($curl);
   
   $rdata=json_decode($response,TRUE);
   $dataset=array();
   foreach($rdata['result'] as $cdata){
     $dataset[]=json_encode($cdata['properties']['link']);
   }
   return '['.implode(',',$dataset).']';
   */
   }

 public function render(): void
  {
    $mode= @$_GET['mode'];    
  // if $type:$type="-".$type; endif;
    //($type == 'webview') ? $type="-".$type."-webview" : $type=$type ;
     ($mode == 'nas_test') ? $mode="-nz-test" : $mode="" ;

    View::render('@base/page/article-page-gallery', $this->getViewData());
  }


}
