<?php declare(strict_types=1);

namespace Everyware\Concepts\Contracts;

/**
 * Interface ProgressHandler
 * @package Everyware\Concepts\Contracts
 */
interface ProgressHandler
{
    /**
     * Determine if in progress on specific key
     *
     * @param string $progressKey
     *
     * @return bool
     */
    public function inProgress(string $progressKey): bool;

    /**
     * Start progress on specific key
     *
     * @param string $progressKey
     *
     */
    public function start(string $progressKey);

    /**
     * Start progress on specific key
     *
     * @param string $progressKey
     */
    public function stop(string $progressKey);

    /**
     * Generate a key specific to this progress handler
     *
     * @param string $progressKey
     *
     * @return string
     */
    public function generateKey(string $progressKey): string;
}
