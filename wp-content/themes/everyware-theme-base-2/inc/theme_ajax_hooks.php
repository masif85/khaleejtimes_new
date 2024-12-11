<?php

use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use EuKit\Base\Parts\NewsMLArticle;
use EuKit\Base\ViewModels\Teaser;
use Infomaker\Everyware\Twig\View;

add_action('wp_ajax_fetch_concept_posts', 'ajax_fetch_concept_posts');
add_action('wp_ajax_nopriv_fetch_concept_posts', 'ajax_fetch_concept_posts');
add_action('wp_ajax_search_page_load_more', 'ajax_search_page_load_more');
add_action('wp_ajax_nopriv_search_page_load_more', 'ajax_search_page_load_more');

function ajax_fetch_concept_posts(): void
{
    $data = $_REQUEST['data'];
    $uuid = sanitize_text_field($data['uuid']);
    $start = (int)$data['start'];

    $query = QueryBuilder::where('ConceptUuids', $uuid)->andIfProperty('Status', 'usable');

    $limit = 20;
    $provider = OpenContentProvider::setup([
        'q'                      => $query->buildQueryString(),
        'contenttypes'           => ['Article'],
        'sort.indexfield'        => 'Pubdate',
        'sort.Pubdate.ascending' => 'false',
        'limit' => $limit,
        'start' => $start,
    ]);

    $provider->setPropertyMap('Article');

    $articles = array_map(static function (NewsMLArticle $article) {
        return (new Teaser($article))->getViewData();
    }, $provider->queryWithRequirements());

    $fetchCount = count($articles);

    $result = [
        'articleList' => View::generate('@base/concept/generate-article-list.twig', ['articles' => $articles]),
        'totalFetchCount' => min($start + $fetchCount, $provider->hits()),
        'hits' => $provider->hits()
    ];

    wp_send_json($result);
}

function ajax_search_page_load_more(): void
{
    $data = $_REQUEST['data'];
    $query = sanitize_text_field($data['q']);
    $start = (int)$data['start'];

    $limit = 20;
    $provider = OpenContentProvider::setup([
        'q' => QueryBuilder::where('contenttype', 'Article')->setText(urldecode($query))->buildQueryString(),
        'contenttypes' => ['Article'],
        'limit' => $limit,
        'start' => $start,
    ]);

    $provider->setPropertyMap('Article');

    $articles = array_map(static function (NewsMLArticle $article) {
        return (new Teaser($article))->getViewData();
    }, $provider->queryWithRequirements());

    $fetchCount = count($articles);

    $result = [
        'articleList' => View::generate('@base/search/generate-article-list.twig', ['articles' => $articles]),
        'totalFetchCount' => min($start + $fetchCount, $provider->hits()),
        'hits' => $provider->hits()
    ];

    wp_send_json($result);
}
