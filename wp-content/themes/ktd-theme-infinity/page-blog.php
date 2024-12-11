<?php declare(strict_types=1);

/* Template Name: Prayer Timings */

use Infomaker\Everyware\Base\Models\Page;
use KTDTheme\ViewModels\BlogsPage;

$currentPage = Page::current();

if ($currentPage instanceof Page) {
  $page = new BlogsPage($currentPage);

  $page->render();
}
