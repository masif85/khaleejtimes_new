<?php

use EuKit\Base\Parts\Concept;
use EuKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Support\NewRelicLog;
use Infomaker\Everyware\Twig\View;
use EuKit\Base\Parts\NewsMLArticle;
use EuKit\Base\ViewModels\ConceptPage;
use Infomaker\Everyware\Base\Utilities;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;

$currentPage = Page::current();

if (!($currentPage instanceof Page)) {
    Utilities::trigger404();
}

try
{
    $concept = Concept::createFromPost($currentPage);
}
catch(UnexpectedValueException $exception)
{
    NewRelicLog::logException($exception);
    Utilities::trigger404();
}

$conceptPage = new ConceptPage($concept);
$uuid = $conceptPage->getViewData()['uuid'];
$query = QueryBuilder::where('ConceptUuids', $uuid)->andIfProperty('Status', 'usable');
$limit = 14;
$provider = OpenContentProvider::setup([
    'q' => $query->buildQueryString(),
    'contenttypes' => ['Article'],
    'sort.indexfield' => 'Pubdate',
    'sort.Pubdate.ascending' => 'false',
    'limit' => $limit,
    'start' => 0,
]);
$provider->setPropertyMap('Article');

$articles = array_map(static function (NewsMLArticle $article) {
    return (new Teaser($article))->getViewData();
}, $provider->queryWithRequirements());

$fetchCount = count($articles);

// Use first 4 articles as top articles
$topArticles = array_splice($articles, 0, 4);

View::render('@base/page/concept-page', array_replace($conceptPage->getViewData(), [
    'loadMore' => $fetchCount < $provider->hits(),
    'fetchCount' => $fetchCount,
    'articles' => $articles,
    'currentPage' => $currentPage,
    'topArticles' => $topArticles
]));
