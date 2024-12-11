<?php declare (strict_types = 1);

namespace KTDTheme\ViewModels\Article;

use EuKit\Base\Parts\Image;
use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\NewsML\Item;
use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Base\ArticleBodyParser;
use KTDTheme\ArticleBodyPresentation;
use KTDTheme\ViewModels\CinemaListingPage;

class CinemaListingArticlePage extends ArticlePage
{
  protected $teaserRatio = '25:36';

  protected function default(): void
  {
    parent::default();

    $page = CinemaListingPage::getPage();

    if ($page) {
      $language = $this->getViewData()['cinemalisting_language'] ?? null;
      $url = get_permalink($page->ID);
      $this->setViewData('date_control', CinemaListingPage::getDateControl($page->ID));
      $this->setViewData('language_url', $language ? add_query_arg(['language' => $language], $url) : $url);
    }

    $this->setViewData('tealiumGroup', 'cinema-landing-page');
  }

  protected function getBody(NewsMLArticle $article): ArticleBodyParser
  {
    $articleBody = $article->getParsedBody();
    $articleBodyPresentation = new ArticleBodyPresentation($article);

    // Set first image in body as main image if teaser is empty
    if (!$mainImage = $this->getViewData()['teaserImage'] ?? null) {
      $imageType = 'x-im/image';
      if ($articleBody->hasType($imageType)) {
        $image = new Image($articleBody->firstOfType($imageType));
        $image->setRatio($this->teaserRatio);
        $mainImage = $image->getViewData();
      }
    }
    
    $this->setViewData('mainImage', $mainImage);

    $trailer = $articleBody->firstOfType('x-im/youtube');
    $gallery = $articleBody->firstOfType('x-im/imagegallery');
    $embed = $articleBody->firstOfType('x-im/htmlembed');

    if ($trailer) {
      $this->setViewData('trailer', $this->renderTrailer($trailer));
    }

    if ($gallery) {
      $this->setViewData('gallery', $articleBodyPresentation->generateItem('x-im/imagegallery', $gallery));
    }
    
    if ($embed) {
      $this->setViewData('embed', $articleBodyPresentation->generateItem('x-im/htmlembed', $embed));
    }

    return $articleBody;
  }

  protected function getOther(NewsMLArticle $article): void
  {
    // Nothing to add
  }

  private function renderTrailer(Item $item): string
  {
    return View::generate('@base/article/trailer', $item->toArray());
  }

  public function render(): void
  {
    View::render('@base/page/article-cinema-listing-page', array_replace(
      $this->getViewData(),
      CinemaListingPage::getCinemaLocations()
    ));
  }
}
