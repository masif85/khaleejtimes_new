<?php declare(strict_types=1);

namespace KTDTheme\ViewModels\Amp;

use Exception;
use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\NewsML\Item;
use Infomaker\Everyware\Support\NewRelicLog;
use EuKit\Base\Parts\ArticleBodyPresentation;
use Infomaker\Everyware\NewsML\Objects\ImageItem;
use KTDTheme\ViewModels\Image;

class AmpArticleBodyPresentation extends ArticleBodyPresentation
{
  /**
   * @var int
   */
  public static $currentImageIndex = 0;

  protected function image(ImageItem $item) : string
  {
    $image = new Image($item);

    return $this->generateArticlePart('image', array_merge($image->getViewData(), [
      'src' => $image->getRatioUrl(AmpArticlePage::IMAGE_WIDTH),
      'isFirstImage' => (self::$currentImageIndex++) === 0,
    ]));
  }

  private function galleryImage(ImageItem $item) : array
  {
    $image = new Image($item);

    return array_replace($image->getViewData(), [
      'src' => $image->getRatioUrl(AmpArticlePage::IMAGE_WIDTH),
    ]);
  }

  protected function imageGallery(Item $item) : string
  {
    $images = array_map([&$this, 'galleryImage'], (array) $item->get('images'));

    $item->set('images', $images);

    return $this->generateArticlePart('gallery', $item->toArray());
  }

  protected function htmlEmbed(Item $item) : string
  {
    if (preg_match("|api.soundcloud.com/tracks/(?<trackId>\d+)|", $item->content, $soundcloud)) {
      return $this->generateArticlePart('soundcloud', ['trackId' => $soundcloud['trackId']]);
    }

    if (preg_match("|/78059622/Responsive-Article-Inarticle-MPU|", $item->content, $slot)) {
      return $this->generateArticlePart('inarticle-ad', ['slot' => $slot[0]]);
    }

    return $this->generateArticlePart('html-embed', $item->toArray());
  }


  protected function instagram(Item $item) : string
  {
    preg_match("|/p/(?<code>[A-Za-z0-9_\-]+)/?|", $item->url, $url);

    return $this->generateArticlePart('instagram', [
      'shortcode' => $url['code']
    ]);
  }

  protected function twitter(Item $item) : string
  {
    return $this->generateArticlePart('twitter', [
      'tweetId' => str_replace('im://tweet/', '', $item->uri)
    ]);
  }

  protected function generateArticlePart($part, $data) : string
  {
    if ($part == "table") {
      $data = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $data);
    }
    try {
      return View::generate("@base/article/amp/{$part}", $data);
    } catch (Exception $e) {
      NewRelicLog::error('Failed to generate article body part', $e);
    }

    return '';
  }
    protected function element(Item $item): string
    {
        $content = $this->isListElement($item) ? $this->generateListContent($item) : $item->get('content');

        return $this->generateArticlePart('element', [
            'type' => str_replace('x-im/', '', $item->getType()),
            'content' => $content ? htmlspecialchars_decode($content) : '',
            'variation' =>  $this->getVarationPartBody($item)
        ]);
    }
    private function getVarationPartBody(Item $item): string
    {
        return $item->getItemAttributes()['variation'] ?? '';
    }

}

