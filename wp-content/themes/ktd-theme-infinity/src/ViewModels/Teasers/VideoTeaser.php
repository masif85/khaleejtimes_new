<?php declare (strict_types = 1);

namespace KTDTheme\ViewModels\Teasers;

use Infomaker\Everyware\Support\Interfaces\PropertyObject;

class VideoTeaser extends Teaser
{
  public function __construct(PropertyObject $article)
  {
    parent::__construct($article);
    
    $this->setViewData('videolength', $article->videolength);
    $this->setViewData('videoId', $article->videoid);
  }
}
