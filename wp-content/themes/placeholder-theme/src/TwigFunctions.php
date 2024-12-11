<?php
declare(strict_types=1);

namespace PlaceholderTheme;

use Everyware\Plugin\SettingsParameters\SettingsParameter;
use Infomaker\Everyware\Base\Sidebars;
use Infomaker\Everyware\Base\Utilities;
use Infomaker\Everyware\Twig\View;

/**
 * TwigFunctions
 *
 * @package Customer\Utilities
 */
class TwigFunctions
{
    public function inProduction(): bool
    {
        return is_prod();
    }

    public function renderClasses($classes): string
    {
        return implode(' ', array_filter((array)$classes));
    }

    public function theContent(): void
    {
        while (have_posts()) {
            the_post();
            the_content();
        }
    }

    public function renderPartial(string $template, array $data = []): void
    {
        View::render($template, $data);
    }

    public function settingsParameter(string $key): ?string
    {
        return SettingsParameter::getValue($key);
    }

    public function renderSidebar(string $id): void
    {
        Sidebars::render($id);
    }

    public function getTemplateDirUri(bool $childTheme = false): string
    {
        return Utilities::getCdnUri();
    }
}
