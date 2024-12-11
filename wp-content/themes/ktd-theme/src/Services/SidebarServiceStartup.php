<?php declare(strict_types=1);

namespace KTDTheme\Services;

use EuKit\Base\Interfaces\ThemeServiceStartup;
use Infomaker\Everyware\Base\Sidebars;

class SidebarServiceStartup implements ThemeServiceStartup
{
  public function setup(): void
  {
    $sidebars = [
      'prayer-timing-sidebar' => [
        'name' => 'Prayer timing sidebar',
        'description' => 'Sidebar to display on prayer timing page',
        'before_widget' => '',
        'after_widget' => '',
      ],
      'gold-forex-sidebar' => [
        'name' => 'Gold/forex sidebar',
        'description' => 'Sidebar to display on Gold/forex page',
        'before_widget' => '',
        'after_widget' => '',
      ],
      'article-infinite-scroll-sidebar' => [
        'name' => 'Article infinite scroll sidebar',
        'description' => 'Article infinite scroll sidebar',
        'before_widget' => '',
        'after_widget' => '',
      ],
      'default-sidebar' => [
        'name' => 'Default sidebar',
        'description' => 'Default sidebar',
        'before_widget' => '',
        'after_widget' => '',
      ],
      'default-article-sidebar' => [
        'name' => 'Default article sidebar',
        'description' => 'Default article sidebar',
        'before_widget' => '',
        'after_widget' => '',
      ],
      'default-infinite-tre-article-sidebar' => [
        'name' => 'Default infinite trending article sidebar',
        'description' => 'Default  infinite trending article sidebar',
        'before_widget' => '',
        'after_widget' => '',
      ],
      
       'search-trending-sidebar' => [
        'name' => 'Search trending sidebar',
        'description' => 'Search trending sidebar',
        'before_widget' => '',
        'after_widget' => '',
      ],

      'default-infinite-rec-article-sidebar' => [
        'name' => 'Default infinite recommended article sidebar',
        'description' => 'Default infinite recommended article sidebar',
        'before_widget' => '',
        'after_widget' => '',
      ],
      'listing-page-sidebar' => [
        'name' => 'Listing Page sidebar',
        'description' => 'Listing Page sidebar',
        'before_widget' => '',
        'after_widget' => '',
      ],

      'video-article-below' => [
        'name' => 'Video article below',
        'description' => 'Video article below',
        'before_widget' => '',
        'after_widget' => '',
      ],
      'leftside-default-article-sidebar' => [
        'name' => 'Leftside default article sidebar',
        'description' => 'Leftside default article sidebar',
        'before_widget' => '',
        'after_widget' => '',
      ],

       'middle-recommended-article-mobile' => [
        'name' => 'Middle recommended article mobile',
        'description' => 'Middle recommended article mobile',
        'before_widget' => '',
        'after_widget' => '',
      ],

      'gallery-article-below' => [
        'name' => 'Gallery article below',
        'description' => 'Gallery article below',
        'before_widget' => '',
        'after_widget' => '',
      ],
      'cinema-listing-sidebar' => [
        'name' => 'Cinema listing sidebar',
        'description' => 'Cinema listing sidebar',
        'before_widget' => '',
        'after_widget' => '',
      ],
      'cinema-listing-article-sidebar' => [
        'name' => 'Cinema listing Article sidebar',
        'description' => 'Cinema listing Article sidebar',
        'before_widget' => '',
        'after_widget' => '',
      ],
      'subsection-sidebar' => [
        'name' => 'Subsection sidebar',
        'description' => 'Subsection sidebar',
        'before_widget' => '',
        'after_widget' => '',
      ],
    ];

    foreach ($sidebars as $id => $sidebar) {
      Sidebars::register($id, $sidebar);
    }
  }
}
