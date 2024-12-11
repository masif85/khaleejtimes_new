<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Interfaces;

interface SettingsPageInterface
{
    public function getStatusMessage(): string;

    public function getTabSlug(): string;

    public function getTabTitle(): string;

    public function onPageLoad(): void;

    public function pageContent(): string;
}
