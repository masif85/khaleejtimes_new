<?php declare(strict_types=1);

/**
 * Template Name: Search Page
 */

use EuKit\Base\Parts\NewsMLArticle;
use EuKit\Base\ViewModels\Page as SearchPage;
use EuKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Support\GenericPropertyObject;
use Infomaker\Everyware\Twig\View;
use Tightenco\Collect\Support\Arr;

const OC_SORT_ASC = 'asc';
const OC_SORT_DESC = 'desc';

const DEFAULT_LIMIT = 20;

$GLOBALS['wp_query']->is_search = true;

$query = Arr::get($_GET, 'q', '');
$sort = Arr::get($_GET, 'sort', \OC_SORT_DESC);

// off-by-one! (OC is 0-based, we use 1-based for UX reasons)
$from = Arr::get($_GET, 'from', 1);

if ( ! \is_numeric($from)) {
    $from = 1;
}

$limit = Arr::get($_GET, 'limit', \DEFAULT_LIMIT);

if ( ! \is_numeric($limit)) {
    $limit = \DEFAULT_LIMIT;
}
if ($limit > 100) {
    $limit = 100;
}

$format = Arr::get($_GET, 'format', 'html');

$sortAscending = ($sort === \OC_SORT_ASC);

$currentPage = Page::current();

if ($currentPage instanceof Page) {

    $searchPage = new SearchPage(new GenericPropertyObject());
    $provider = OpenContentProvider::setup([
            'q' => QueryBuilder::where('contenttype', 'Article')->setText(urldecode($query))->buildQueryString(),
            'contenttypes' => ['Article'],
            'limit' => $limit,
            'start' => $from - 1,
        ]
    );

    $provider->setPropertyMap('Article');

    $articles = array_map(static function (NewsMLArticle $article) {
        return (new Teaser($article))->getViewData();
    }, $provider->queryWithRequirements());

    $fetchCount = count($articles);

    View::render('@base/page/search', array_replace($searchPage->getViewData(), [
        'loadMore' => $fetchCount < $provider->hits(),
        'fetchCount' => $fetchCount,
        'hits' => $provider->hits(),
        'articles' => $articles,
        'currentPage' => $currentPage,
        'query' => urldecode($query),
        'loadMoreAction' => 'search_page_load_more'
    ]));
}
