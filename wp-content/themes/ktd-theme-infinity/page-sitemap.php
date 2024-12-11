<?php declare(strict_types=1);

/**
 * Template Name: Sitemap Page
 */

use Infomaker\Everyware\Base\Models\Page;
use Everyware\Plugin\Sitemap\Sitemap;

$currentPage = Page::current();

if ($currentPage instanceof Page) {
  $sitemapType = get_query_var('sitemap_type');
  $sitemapYear = get_query_var('sitemap_year');
  $sitemapMonth = get_query_var('sitemap_month');
  $sitemapLang = get_query_var('sitemap_lang');

  $sitemap = Sitemap::init($sitemapType, $sitemapYear, $sitemapMonth, $sitemapLang);

  foreach ($sitemap->getHeaders() as $header => $value) {
    header("$header: $value");
  }

  echo $sitemap->getXml();
}
