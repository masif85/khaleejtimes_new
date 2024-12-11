<?php declare(strict_types=1);

namespace PlaceholderTheme;

use EuKit\Base\Startup as BaseStartup;
use Infomaker\Everyware\Twig\ViewSetup;
use PlaceholderTheme\Services\TwigServiceStartup;

/**
 * Class Startup
 * @package PlaceholderTheme
 */
class Startup extends BaseStartup
{
    public function bootstrap(): void
    {
        parent::bootstrap();

        $twigService = new TwigServiceStartup(ViewSetup::getInstance());
        $twigService->setup();
    }
}
