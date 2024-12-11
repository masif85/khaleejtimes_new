<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Interfaces;

interface ComponentsPageInterface
{
    public function activateComponents(): bool;

    public function deactivateComponents(): void;

    public function getSlug(): string;

    public function getTitle(): string;

    public function addToMenu(string $parentSlug): void;
}
