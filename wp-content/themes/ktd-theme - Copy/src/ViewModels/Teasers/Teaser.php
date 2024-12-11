<?php declare (strict_types = 1);

namespace KTDTheme\ViewModels\Teasers;

use EuKit\Base\ViewModels\BaseObject;
use Infomaker\Everyware\NewsML\Objects\TeaserItem;
use Infomaker\Everyware\NewsML\Parsers\ImageParser;
use Infomaker\Everyware\NewsML\Parsers\ImageGalleryParser;
use Infomaker\Everyware\Support\GenericPropertyObject;
use Infomaker\Everyware\Support\Interfaces\PropertyObject;
use KTDTheme\ViewModels\Image;

class Teaser extends BaseObject
{
  protected $ratio = '16:9';

  public function __construct(PropertyObject $article)
  {
    $propertyObject = new GenericPropertyObject();
    $propertyObject->fill([
      'uuid' => $article->getSingleValue('uuid'),
      'pubdate' => $article->getPubDate()->diffForHumans(),
      'headline' => $article->getSingleValue('headline'),
      'leadin' => $article->getSingleValue('leadin'),
      'authors' => $article->authors,
      'permalink' => $article->getSingleValue('permalink'),
      'sectionName' => $article->getSingleValue('sections'),
      'teaserbody' => $article->teaser_body,
      'contentsubtype' => $article->contentsubtype,
      'conceptsectionuuids' => $article->conceptsectionuuids,
    ]);

    $propertyObject->fill($this->createTeaser($article));

    parent::__construct($propertyObject);
  }

  protected function createTeaser(PropertyObject $article): array
  {
    $teaser = $article->getNewsMLTeaser() ?: null;
    $image = $this->getMainImage($article, $teaser);
    if ($image) {
      $image->setRatio($this->ratio);
    }

    return array_filter(
      array_replace($teaser ? $teaser->toArray() : [], [
        'image' => $image ? $image->getViewData() : null,
      ])
    );
  }

  protected function getMainImage(PropertyObject $article, TeaserItem $teaser = null): ?Image
  {
    if ($teaser && $teaser->hasImage()) {
      return new Image($teaser->getImage());
    }

    $bodyParser = $article->getBodyParser();

    // get first image in body
    if ($image = $bodyParser->firstOfType(ImageParser::OBJECT_TYPE)) {
      return new Image($image);
    }

    // get first image in gallery
    if ($gallery = $bodyParser->firstOfType(ImageGalleryParser::OBJECT_TYPE)) {
      $images = $gallery->getItemAttribute('images');
      if ($images) {
        return new Image(reset($images));
      }
    }
    
    return null;
  }
}
