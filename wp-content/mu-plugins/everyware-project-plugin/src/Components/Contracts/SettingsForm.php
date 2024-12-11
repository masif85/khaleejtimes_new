<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components\Contracts;

/**
 * Interface FormView
 * @package Everyware\ProjectPlugin\Components\Contracts
 */
interface SettingsForm
{
    public function currentVersion(): string;
    public function getComponentInfo(array $componentInfoMap): array;
    public function getDescription(): string;
    public function getName(): string;

    public function create(array $storedData):string;
    public function edit(array $storedData):string;
    public function getFormPrefix(): string;
}
