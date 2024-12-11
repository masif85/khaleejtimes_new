<?php declare(strict_types=1);

namespace KTDTheme\Services;

use EuKit\Base\Interfaces\ThemeServiceStartup;

class WidgetServiceStartup implements ThemeServiceStartup
{
  /**
   * Setup theme widgets
   *
   * @return void
   */
  public function setup(): void
  {
    add_action('widgets_init', [&$this, 'deregisterWidgets']);
  }

  public function deregisterWidgets()
  {
    unregister_widget('WP_Widget_Custom_HTML');
  }
}
