<?php

use EuKit\Base\Parts\NewsMLArticle;
use KTDTheme\ViewModels\Article\ArticlePage;
use KTDTheme\ViewModels\Article\StorytellingArticlePage;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\Utilities;

$currentPage = Page::current();

if (!($currentPage instanceof Page)) {
  Utilities::trigger404();
}

$article = NewsMLArticle::createFromPost($currentPage);

if (!$article) {
  Utilities::trigger404();
}

if ($article->get('contentsubtype') == 'STORYTELLING') {
    $articlePage = StorytellingArticlePage::createFrom($article);
} else {
    $articlePage = ArticlePage::createFrom($article);
}

$articleData = $articlePage->getViewData();

$articlePage->render();
