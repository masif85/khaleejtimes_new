<?php declare (strict_types=1);

namespace KTDTheme\ViewModels\Amp;

use Infomaker\Everyware\Twig\View;
use EuKit\Base\Parts\NewsMLArticle;
use KTDTheme\ViewModels\Article\ArticlePage;
use Infomaker\Everyware\Base\ArticleBodyParser;
use Infomaker\Everyware\NewsML\Parsers\ImageParser;
use Infomaker\Everyware\NewsML\Parsers\ImageGalleryParser;
use KTDTheme\ViewModels\Image;

class AmpArticlePage extends ArticlePage
{
  const IMAGE_WIDTH = 540;

  protected function getBody(NewsMLArticle $article) : ArticleBodyParser
  {
    $bodyParser = $article->getBodyParser();

    // Preload Images must come before generating content
    $this->setViewData('preloadImages', $this->getAllImages($bodyParser));

    $this->setViewData('vuukleSource', $this->getVuukleSource($article));

    $this->setViewData('structuredData', $this->getStructuredData($article));

    $this->setViewData('mainImage', ['src' => $this->getMainImage($article)]);

    $this->setViewData('body', $bodyParser->generateContent(
      new AmpArticleBodyPresentation($article))
    );

    return $bodyParser;
  }

  private function getMainImage(NewsMLArticle $article, $ratio = 1200) : string
  {
    if (($teaser = $article->getNewsMLTeaser()) && $teaser->hasImage()) {
      return (new Image($teaser->getImage()))->getRatioUrl($ratio);
    }

    $bodyParser = $article->getBodyParser();

    if ($bodyParser->hasType(ImageParser::OBJECT_TYPE)) {
      return (new Image($bodyParser->firstOfType(ImageParser::OBJECT_TYPE)))->getRatioUrl($ratio);
    }

    if ($gallery = $bodyParser->firstOfType(ImageGalleryParser::OBJECT_TYPE)) {
      $images = $gallery->getItemAttribute('images');
      if ($images) {
        return (new Image(reset($images)))->getRatioUrl($ratio);
      }
    }

    return '';
  }

  private function getAllImages(ArticleBodyParser $bodyParser) : array
  {
    $images = [];

    // Article Images
    foreach ($bodyParser->allOfType(ImageParser::OBJECT_TYPE) as $image) {
      $images[] = (new Image($image))->getRatioUrl(self::IMAGE_WIDTH);
    }

    // Gallery Images
    foreach ($bodyParser->allOfType(ImageGalleryParser::OBJECT_TYPE) as $gallery) {
      foreach ($gallery->getItemAttribute('images') as $image) {
        $images[] = (new Image($image))->getRatioUrl(self::IMAGE_WIDTH);
      }
    }

    return $images;
  }

  private function getVuukleSource(NewsMLArticle $article) : string
  {
    $query = http_build_query([
      'url' => get_permalink(),
      'host' => 'khaleejtimes.com',
      'id' => $article->oldGuid ?: strtoupper($article->uuid),
      'apiKey' => '841fb3e5-977f-4e2e-be39-fae608323cc5',
      'title' => get_the_title(),
      'img' => $this->getMainImage($article, self::IMAGE_WIDTH),
    ]);

    return $query;
  }

  private function getStructuredData(NewsMLArticle $article) : array
  {

      $LAuthor = $article->getAuthors()->map(function ($author) {
          return ['@type' => 'Person', 'name' => $author->name];
      });
      

      if (!isset($LAuthor[0])) {
          if($article->author_byline != "")
              $LAuthor[0] = ['@type' => 'Person', 'name' => $article->author_byline];
          else
              $LAuthor[0] = ['@type' => 'Person', 'name' => 'None'];
      }

    return [
      '@context' => 'https://schema.org',
      '@type' => 'NewsArticle',
      'inLanguage' => 'en',
      'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => 'https://www.khaleejtimes.com/',
      ],
      'name' => get_the_title(),
      'description' => '',
      'headline' => $article->headline,
      'articleSection' => $article->getSingleValue('section'),
      'keywords' => $article->getTags()->pluck('name')->implode(', '),
      'thumbnailUrl' => $this->getMainImage($article, 780),
      'image' => [
        '@type' => 'ImageObject',
        'url' => $this->getMainImage($article),
        'width' => 780,
        'height' => 435
      ],
      'datePublished' => $article->pubdate->format('c'),
      'dateModified' => $article->objectUpdated->format('c'),
      'author' => $LAuthor,
      'publisher' => [
        '@type' => 'Organization',
        'name' => 'Khaleej Times',
        'url' => 'https://www.khaleejtimes.com',
        'logo' => [
          '@type' => 'ImageObject',
          'url' => 'https://images.khaleejtimes.com/assets/png/khaleej-times-logo.png',
          'width' => 200
        ]
      ],
      'copyrightHolder' => [
        'type' => 'Organization',
        'name' => 'Galadari Printing and Publishing LLC'
      ],
      'sameAs' => [
        'https://twitter.com/khaleejtimes',
        'https://www.facebook.com/khaleejtimes',
        'https://www.linkedin.com/company/khaleejtimes',
        'https://www.instagram.com/khaleejtimes/',
        'https://www.youtube.com/c/khaleejtimesvideos',
        'https://en.wikipedia.org/wiki/Khaleej_Times'
      ],
    ];
  }

  /**
   * Render article page view
   *
   * @return void
   */
  public function render() : void
  {
    // turn off NewRelic auto script injection for AMP pages.
    if (extension_loaded('newrelic')) { newrelic_disable_autorum(); }

    View::render('@base/article/amp/article-page', $this->getViewData());
  }
}
