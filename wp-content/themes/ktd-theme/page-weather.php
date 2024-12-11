<?php declare(strict_types=1);

/* Template Name: Weather Page */

use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use KTDTheme\ViewModels\BasePage;

$currentPage = Page::current();

if ($currentPage instanceof Page) {
  $page               = new BasePage($currentPage);

  // Weather Articles
  $provider = OpenContentProvider::setup([
    'q' => QueryBuilder::query('Section:Weather')->buildQueryString(),
    'contenttypes' => ['Article'],
    'sort.indexfield' => 'Pubdate',
    'sort.Pubdate.ascending' => 'false',
    'limit' => 16,
    'Status' => 'usable'
  ]);

  $articles = $provider->queryWithRequirements();

  if (is_array($articles)) {
    add_filter('ew_content_container_fill', static function ($arr) use ($articles) {
      return array_merge($arr, $articles);
    });
  }

  View::render('@base/page/page-weather', array_replace($page->getViewData(), [
    'tealiumGroup' => 'weather'
  ]));
}
