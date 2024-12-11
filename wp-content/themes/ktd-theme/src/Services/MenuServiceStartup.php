<?php declare(strict_types=1);

namespace KTDTheme\Services;

use EuKit\Base\Interfaces\ThemeServiceStartup;
use Infomaker\Everyware\Base\Menus;

class MenuServiceStartup implements ThemeServiceStartup
{
  /**
   * Setup theme menus
   *
   * @return void
   */
  public function setup(): void
  {
    $registerMenus = [
      'meganav' => [
        'id' => 'meganav',
        'description' => __('Mega Nav', 'ewkit')
      ],
      'topmenus' => [
        'id' => 'topmenus',
        'description' => __('Top Menu', 'ewkit')
      ],
      'footernav' => [
        'id' => 'footernav',
        'description' => __('Footer Nav', 'ewkit')
      ],
      'prayertimes' => [
        'id' => 'prayertimes',
        'description' => __('Prayer times', 'ewkit')
      ]
    ];

    Menus::init($registerMenus);
  }
}
