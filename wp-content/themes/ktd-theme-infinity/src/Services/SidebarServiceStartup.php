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
      'video-article-below' => [
        'name' => 'Video article below',
        'description' => 'Video article below',
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
