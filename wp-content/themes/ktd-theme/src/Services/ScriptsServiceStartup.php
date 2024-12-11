<?php declare(strict_types=1);

namespace KTDTheme\Services;

use EuKit\Base\Interfaces\ThemeServiceStartup;

class ScriptsServiceStartup implements ThemeServiceStartup
{
  /**
   * @var string
   */
  private $prefix;

  public function __construct(string $prefix)
  {
    $this->prefix = $prefix;
  }

  /**
   * Setup theme scripts
   *
   * @return void
   */
  public function setup(): void
  {
    add_action('wp_enqueue_scripts', [&$this, 'deregisterScripts']);
  }

  public function deregisterScripts()
  {
    wp_deregister_script("{$this->prefix}-head-js");
    wp_deregister_script("{$this->prefix}-body-js");
    wp_deregister_script("{$this->prefix}-article-js");

    wp_deregister_script('base-head-js');
    wp_deregister_script('base-body-js');
    wp_deregister_script('base-article-js');
  }
}
