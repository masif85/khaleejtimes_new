<?php declare(strict_types=1);


namespace Everyware\Concepts;


use Everyware\Wordpress\TransientCache;

/**
 * Lets you register a lock for something, to prevent two processes from working with it at the same time.
 *
 * @package Everyware\Concepts
 */
class LockHandler
{
    /**
     * @var int How long a lock should last, in seconds.
     */
    private const LOCK_TTL = 5;

    /**
     * @var float How long to wait, if encountering a lock, before trying again. In seconds.
     */
    private const LOCK_WAITTIME = 0.5;

    /**
     * @var int See requestLock() for usage.
     */
    private const WRITE_WAITTIME_MULTIPLIER = 16;

    /**
     * @var TransientCache
     */
    private $lockCache;

    /**
     * @var string Optional prefix that will be prepended to all keys.
     */
    private $keyPrefix;

    /**
     * @var string Identifier for this process.
     */
    private $processId;

    public function __construct(TransientCache $lockCache, string $keyPrefix = '', string $processId = null)
    {
        $this->lockCache = $lockCache;
        $this->keyPrefix = $keyPrefix;
        $this->processId = $processId ?? $this->generateProcessId();
    }

    /**
     * Ask to lock something, to prevent two processes from working with it at the same time.
     *
     * If several processes try to lock the same thing at the same time, only one gets to lock.
     *
     * @param string $key
     *
     * @return bool  TRUE if this process successfully locked it, or already owns the lock. FALSE if some other process has already locked it.
     */
    public function requestLock(string $key): bool
    {
        $lockOwner = $this->getLockOwner($key);

        if ($lockOwner === $this->processId) {
            // We already own this lock.
            return true;
        }

        if ($lockOwner !== null) {
            // Someone else owns this lock.
            return false;
        }

        $startTime = microtime(true);

        $this->lockCache->set($this->getLockKey($key), $this->processId, self::LOCK_TTL);

        // Measure how many microseconds it took to execute the above line.
        $endTime = microtime(true);
        $microseconds = (int)(($endTime - $startTime) * 1000000);

        /*
         * Reload from the lock cache to make sure that it actually retains the value that we assigned to it.
         * If another process has been editing it at the same time, we need to give it time to finish before we look.
         * So sleep for as many microseconds as it took to write, multiplied a few times for good measure.
         */
        usleep($microseconds * self::WRITE_WAITTIME_MULTIPLIER);
        $lockOwner = $this->getLockOwner($key);

        return $lockOwner === $this->processId;
    }

    /**
     * If another process is locking it: try waiting for it to finish.
     *
     * @param string $key
     */
    public function waitWhileLocked(string $key): void
    {
        $i = 0.0;
        while ($i < self::LOCK_TTL && $this->isLocked($key)) {
            usleep((int)(self::LOCK_WAITTIME * 1000000));
            $i += self::LOCK_WAITTIME;
        }
    }

    /**
     * @param string $key
     */
    public function unlock(string $key): void
    {
        $lockOwner = $this->getLockOwner($key);

        // Only the process that "owns" the lock should be allowed to unlock it.
        if ($lockOwner === $this->processId) {
            $this->lockCache->delete($this->getLockKey($key));
        }

    }

    private function generateProcessId(): string
    {
        return getmypid() . '-' . md5((string)mt_rand());
    }

    /**
     * @param string $key
     *
     * @return string|null  ID of the process that owns the lock. Might match $this->processId. NULL if unlocked.
     */
    private function getLockOwner(string $key): ?string
    {
        $value = $this->lockCache->get($this->getLockKey($key));

        return ($value === false) ? null : $value;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    private function isLocked(string $key): bool
    {
        return $this->getLockOwner($key) !== null;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function getLockKey(string $key): string
    {
        return $this->keyPrefix . $key;
    }
}
