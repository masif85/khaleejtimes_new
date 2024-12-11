<?php declare(strict_types=1);

namespace KTDTheme;

use EuKit\Base\Startup as BaseStartup;
use Infomaker\Everyware\Base\AppConfig;
use Infomaker\Everyware\Twig\ViewSetup;
use KTDTheme\Services\TwigServiceStartup;
use KTDTheme\Services\SidebarServiceStartup;
use KTDTheme\Services\MenuServiceStartup;
use KTDTheme\Services\ScriptsServiceStartup;
use KTDTheme\Services\WidgetServiceStartup;

/**
 * Class Startup
 * @package KTDTheme
 */
class Startup extends BaseStartup
{
  public function bootstrap(): void
  {
    parent::bootstrap();

    $this->registerServices();
  }

  /**
   * Register theme services
   *
   * @return void
   */
  private function registerServices(): void
  {
    $services = [
      new TwigServiceStartup(ViewSetup::getInstance()),
      new ScriptsServiceStartup(AppConfig::getOrganisationSlug()),
      new SidebarServiceStartup(),
      new MenuServiceStartup(),
      new WidgetServiceStartup()
    ];

    foreach ($services as $service) {
      $service->setup();
    }
  }
}
