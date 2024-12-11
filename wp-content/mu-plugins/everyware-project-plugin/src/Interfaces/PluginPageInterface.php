<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Interfaces;

interface PluginPageInterface
{
    public function getSlug(): string;

    public function getTitle(): string;

    public function addToMenu(string $parentSlug): void;
}
