<?php

use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use EuKit\Base\ViewModels\BasePage;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Twig\View;

$currentPage = Page::current();

if ($currentPage instanceof Page) {

    $page = new BasePage($currentPage);

    $getSolrQuery = $currentPage->getMeta('ew_solr_query', '');

    if ( ! empty($getSolrQuery)) {

        $provider = OpenContentProvider::setup([
            'q' => $getSolrQuery,
            'contenttypes' => ['Article'],
            'sort.indexfield' => 'Pubdate',
            'sort.Pubdate.ascending' => 'false',
            'start' => 0,
            'limit' => 10
        ]);

        $articles = $provider->queryWithRequirements();

        if (is_array($articles)) {
            add_filter('ew_content_container_fill', static function ($arr) use ($articles) {
                $arr = array_merge($arr, $articles);

                return $arr;
            });
        }
    }

    View::render('@base/page/page', $page->getViewData());
}
