<?php declare (strict_types = 1);

namespace KTDTheme\ViewModels\Article;

use EuKit\Base\Parts\NewsMLArticle;
use Infomaker\Everyware\NewsML\Item;
use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Base\ArticleBodyParser;
use KTDTheme\ViewModels\Image;

class SponsoredArticlePage extends ArticlePage
{
  protected function default(): void
  {
    parent::default();

    $options = get_option('oc_options');
    $this->setViewData('image_endpoint_url', $options['imengine_url'] ?? '');
    $this->setViewData('tealiumGroups', 'sponsored');
  }
  
  protected function getBody(NewsMLArticle $article): ArticleBodyParser
  {
    $articleBody = parent::getBody($article);

    $this->getFullWidthImage($articleBody);

    return $articleBody;
  }

  protected function getTeasers(NewsMLArticle $article): void
  {
    parent::getTeasers($article);

    $teaserXml = simplexml_load_string($article->get('sponsor_teaser_raw'));

    if ($teaserXml) {
      $this->setViewData('sponsorTeaserHeadline', $teaserXml->data->title);
      $this->setViewData('sponsorTeaserLabel', $teaserXml->data->label);
      if ($teaserXml->links->link) {
        $this->setViewData('sponsorTeaserImageLink', $teaserXml->data->link);
        $this->setViewData('sponsorTeaserImageUuid', $teaserXml->links->link->attributes()->uuid);
      }
    }
  }

  private function getFullWidthImage(ArticleBodyParser $articleBody): void
  {
    $image = [];
    foreach ($articleBody->getBody() as $item) {
      // skip items that are not images
      if (!$item instanceof Item || $item->getType() !== 'x-im/image') {
        continue;
      }
      $objectProperties = $item->getItemAttribute('objectProperties', []);
      // skip images that are not full width
      if (
        !array_key_exists('presentation', $objectProperties) ||
        $objectProperties['presentation'] !== 'fullwidth'
      ) {
        continue;
      }
      break;
    }

    if ($item->uri) {
      $image = new Image($item);
      $this->setViewData('fullWidthImage', $image->getViewData());
    }
  }

  public function render(): void
  {
    View::render('@base/page/article-sponsored-page', $this->getViewData());
  }
}
