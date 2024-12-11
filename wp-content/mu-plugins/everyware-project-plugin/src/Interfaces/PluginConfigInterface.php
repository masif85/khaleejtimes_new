<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Interfaces;

interface PluginConfigInterface
{
    public function getName(): string;

    public function getSlug(): string;

    public function getPath(string $file = null): string;
}
