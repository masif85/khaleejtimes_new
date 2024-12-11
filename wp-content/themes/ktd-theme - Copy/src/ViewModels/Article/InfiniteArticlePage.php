<?php declare (strict_types = 1);

namespace KTDTheme\ViewModels\Article;

use Infomaker\Everyware\Twig\View;
use Tightenco\Collect\Support\Arr;

class InfiniteArticlePage extends ArticlePage
{
  protected function default(): void
  {
    parent::default();

    $this->setViewData('skip', Arr::get($_GET, 'skip', ''));
  }

  public function render(): void
  {
    View::render('@base/page/page-infinite-article', $this->getViewData());
  }
}
