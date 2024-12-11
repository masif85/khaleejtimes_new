<?php declare(strict_types=1);

namespace Everyware\Concepts;

use Everyware\Concepts\Contracts\ProgressHandler;
use Everyware\Concepts\Contracts\SimpleCacheHandler;
use Infomaker\Everyware\Support\NewRelicLog;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class ProgressHandler
 * @package Everyware\Concepts
 */
class Progress implements ProgressHandler
{
    public const PROCESSING = 'processing';

    /**
     * @var SimpleCacheHandler
     */
    private $cache;

    /**
     * Max progress time in seconds
     * @var int
     */
    private $maxProgress;

    public function __construct(SimpleCacheHandler $cache, $maxProgress = 60)
    {
        $this->cache = $cache;
        $this->maxProgress = 60 * $maxProgress;
    }

    /**
     * Determine if in progress on specific key
     *
     * @param string $progressKey
     *
     * @return bool
     */
    public function inProgress(string $progressKey): bool
    {
        try {
            return $this->cache->get($this->generateKey($progressKey)) === self::PROCESSING;
        } catch (InvalidArgumentException $e) {
            NewRelicLog::error($e->getMessage(), $e);
        }

        return false;
    }

    /**
     * Start progress on specific key
     *
     * @param string $progressKey
     */
    public function start(string $progressKey): void
    {
        try {
            $this->cache->set($this->generateKey($progressKey), self::PROCESSING, $this->maxProgress);
        } catch (InvalidArgumentException $e) {
            NewRelicLog::error($e->getMessage(), $e);
        }
    }

    /**
     * Start progress on specific key
     *
     * @param string $progressKey
     */
    public function stop(string $progressKey): void
    {
        try {
            $this->cache->delete($this->generateKey($progressKey));
        } catch (InvalidArgumentException $e) {
            NewRelicLog::error($e->getMessage(), $e);
        }
    }

    /**
     * Generate a key specific to this progress handler
     *
     * @param string $progressKey
     *
     * @return string
     */
    public function generateKey(string $progressKey): string
    {
        return md5('concept_processing_' . $progressKey, true);
    }
}
