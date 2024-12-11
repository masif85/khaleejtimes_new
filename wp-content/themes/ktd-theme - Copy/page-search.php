<?php declare(strict_types=1);

/**
 * Template Name: Search Page
 */

use Infomaker\Everyware\Base\Models\Page;
use KTDTheme\ViewModels\SearchPage;

$currentPage = Page::current();

if ($currentPage instanceof Page) {

  $page = new SearchPage($currentPage);

  $page->render();
}
