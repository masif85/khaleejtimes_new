<?php

namespace KTDTheme\ViewModels;

use EuKit\Base\Parts\SimpleImage;
use Infomaker\Imengine\Imengine;
use Infomaker\Everyware\Support\Interfaces\PropertyObject;

class Concept extends \EuKit\Base\Parts\Concept
{
  public function __construct(PropertyObject $object)
  {
    parent::__construct($object);
  }

  public function getAvatar(): ?SimpleImage
  {
    $uuid = $this->getMultiValue('image_uuids');

    if (empty($uuid)) {
      return null;
    }

    $width = 300;
    $height = 300;

    return new SimpleImage([
      'src' => Imengine::thumbnail($width, $height)->with(['format' => 'png'])->fromUuid($uuid[0]),
      'width' => $width,
      'height' => $height,
    ]);
  }
}
