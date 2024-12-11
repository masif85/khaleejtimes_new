<?php declare(strict_types=1);

/* Template Name: Cinema listing */

use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Base\Models\Page;
use KTDTheme\ViewModels\CinemaListingPage;

$currentPage = Page::current();

if ($currentPage instanceof Page) {
  $page = new CinemaListingPage($currentPage);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wp_send_json_success([
      'html' => View::generate('@base/page/part/cinema-listing/movies', $page->getViewData())
    ]);
  }

  $page->getLanguages();

  $page->render();
}
