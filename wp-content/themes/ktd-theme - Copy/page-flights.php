<?php declare(strict_types=1);

/* Template Name: Airport Flights and Search */

use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use KTDTheme\ViewModels\BasePage;

$currentPage = Page::current();

if ($currentPage instanceof Page) {
  $page = new BasePage($currentPage);

  $menu = [
    'dubai' => 'United Arab Emirates',
    'riyadh' => 'Saudi Arabia',
    'doha' => 'Qatar',
    'muscat' => 'Oman',
    'kuwait' => 'Kuwait',
    'manama' => 'Bahrain',
  ];

  $grouped = [
    'dubai' => ['dubai', 'abu-dhabi', 'sharjah', 'ras-al-Khaimah'],
    'riyadh' => ['riyadh', 'jeddah', 'dammam', 'medinah']
  ];

  $flights = [];

  foreach ($grouped as $group) {
    if (in_array($currentPage->post_name, $group)) {
      $flights = $group;
    }
  }

  $page->setViewData('flights', [
    'menu' => $menu,
    'all' => $flights,
    'iframe' => get_post_meta($currentPage->id, 'iframe_url', $single = true)
  ]);

  // Aviation Articles
  $provider = OpenContentProvider::setup([
    'q' => QueryBuilder::query('Section:Aviation')->buildQueryString(),
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

  View::render('@base/page/page-flights', array_replace($page->getViewData(), [
    'tealiumGroup' => 'flight-status'
  ]));
}
