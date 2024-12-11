<?php

use Infomaker\Everyware\Twig\View;
use EuKit\Base\Parts\NewsMLArticle;
use EuKit\Base\ViewModels\ArticlePage;
use Infomaker\Everyware\Base\Models\Page;

$current_page = Page::current();

if ($current_page instanceof Page) {

    $article = NewsMLArticle::createFromPost($current_page);
    $articlePage = new ArticlePage($article);

    View::render('@base/page/article-page', $articlePage->getViewData());
}
