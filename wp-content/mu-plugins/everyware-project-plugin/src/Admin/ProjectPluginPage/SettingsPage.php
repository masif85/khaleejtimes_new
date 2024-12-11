<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Admin\ProjectPluginPage;

use Infomaker\Everyware\Support\Str;
use Everyware\ProjectPlugin\Admin\Abstracts\TabsPage;

class SettingsPage extends TabsPage
{
    protected static $title = 'Settings';

    public function __construct()
    {
        $this->setDescription('This is the settings panel for your project.');
    }

    public function getSlug(): string
    {
        return Str::slug(static::$title);
    }

    public function getTitle(): string
    {
        return Str::title(static::$title);
    }

    protected function getPageSlug(string $parentSlug): string
    {
        return Str::slug($parentSlug);
    }
}
