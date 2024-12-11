<?php declare(strict_types=1);

namespace Everyware\ProjectPlugin\Components\Contracts;

interface Admin
{
    /**
     * Uses the dock-head of the component to extract information
     *
     * @param array $componentInfoMap
     *
     * @return array
     */
    public function getComponentInfo(array $componentInfoMap): array;

    public function getDescription(): string;
    public function getId(): string;
    public function getInputPrefix(): string;
    public function getName(): string;
    public function getSettings(): array;

    /**
     * Fires on creation
     *
     * @param array $storedData
     *
     * @return string The settings form.
     */
    public function create(array $storedData): string;

    /**
     * Fires on edit
     *
     * @param array $storedData
     *
     * @return string The settings form.
     */
    public function edit(array $storedData): string;

    /**
     * Fires before the data will be saved
     *
     * @param array $newData
     * @param array $oldData
     *
     * @return bool
     */
    public function store(array $newData, array $oldData = []): bool;

    /**
     * Fires before the data will be updated
     *
     * @param array $newData
     * @param array $oldData
     *
     * @return bool
     */
    public function update(array $newData, array $oldData = []): bool;
}
