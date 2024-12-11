<?php declare(strict_types=1);

namespace KTDTheme;

use EuKit\Base\Parts\ArticleBodyPresentation as BaseArticleBodyPresentation;
use Infomaker\Everyware\NewsML\Item;
use Infomaker\Everyware\NewsML\Objects\ImageItem;
use Infomaker\Everyware\Support\Str;
use KTDTheme\ViewModels\Image;

class ArticleBodyPresentation extends BaseArticleBodyPresentation
{
  protected function imageGallery(Item $item): string
  {
    $images = array_map([&$this, 'galleryImage'], (array)$item->get('images'));
    $item->set('images', $images);

    return $this->generateArticlePart('gallery', $item->toArray());
  }

  protected function contentPart(Item $item): string
  {
    $item->set('data', $item->getFirstChild('data'));

    if (Str::contains($item->get('content_type', ''), 'quote')) {
      $image = $this->getContentPartImage($item);

      $item->set('image', $image ? $image->getViewData() : null);
    }

    return $this->generateArticlePart('content-part', $item->toArray());
  }

  private function galleryImage(ImageItem $item): array
  {
    $image = new Image($item);

    return $image->getViewData();
  }

  private function getContentPartImage(Item $item): ?Image
  {
    $images = array_filter($item->get('links', []), static function (Item $item) {
      return $item->getType() === 'x-im/image';
    });

    $image = array_shift($images);

    return $image ? new Image($image) : null;
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
