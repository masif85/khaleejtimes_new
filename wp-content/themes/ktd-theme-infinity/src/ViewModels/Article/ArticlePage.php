<?php declare (strict_types = 1);

namespace KTDThemeInfinity\ViewModels\Article;

use EuKit\Base\ViewModels\RelatedConcept;
use EuKit\Base\Parts\NewsMLArticle;
use EuKit\Base\Parts\Concept;
use Infomaker\Everyware\Base\ArticleBodyParser;
use KTDThemeInfinity\ViewModels\Amp\AmpArticlePage;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Base\ThemeMods;
use Infomaker\Everyware\NewsML\Parsers\ImageParser;
use Infomaker\Everyware\NewsML\Parsers\ImageGalleryParser;
use Infomaker\Everyware\Support\Arr;
use Infomaker\Everyware\Support\GenericPropertyObject;
use Infomaker\Everyware\Twig\View;
use KTDThemeInfinity\ArticleBodyPresentation;
use KTDThemeInfinity\ViewModels\CinemaListingPage;
use KTDThemeInfinity\ViewModels\Page;
use KTDThemeInfinity\ViewModels\Teasers\Teaser;
use Tightenco\Collect\Support\Collection;
use Infomaker\Everyware\NewsML\Objects\TeaserItem;
use KTDThemeInfinity\ViewModels\Image;

class ArticlePage extends Page
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
 
 public function render(): void
  {
    $mode= @$_GET['mode'];    
  // if $type:$type="-".$type; endif;
    //($type == 'webview') ? $type="-".$type."-webview" : $type=$type ;
     ($mode == 'nas_test') ? $mode="-nz-test" : $mode="" ;

    View::render('@base/page/article-page'.$mode, $this->getViewData());
  }


}
