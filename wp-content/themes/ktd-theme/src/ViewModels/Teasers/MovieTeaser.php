<?php declare (strict_types = 1);

namespace KTDTheme\ViewModels\Teasers;

use Infomaker\Everyware\Support\Interfaces\PropertyObject;

class MovieTeaser extends Teaser
{
  protected $ratio = '25:36';

  public function __construct(PropertyObject $article)
  {
    parent::__construct($article);
    
    $this->setViewData('cinemaListingLanguage', $article->cinemalisting_language);
    $this->setViewData('cinemaListingMpaa', $article->cinemalisting_mpaa);
    $this->setViewData('pubdate', $article->getPubDate());
  }
}
