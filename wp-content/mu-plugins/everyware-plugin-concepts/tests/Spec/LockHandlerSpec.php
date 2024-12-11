<?php

namespace Spec\Everyware\Concepts;

use Everyware\Concepts\LockHandler;
use Everyware\Wordpress\TransientCache;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @method requestLock(string $ley)
 * @method waitWhileLocked(string $key)
 * @method unlock(string $key)
 */
class LockHandlerSpec extends ObjectBehavior
{
    private const KEY_PREFIX = 'prefix_';

    private const PROCESS_ID = 'processId';

    /**
     * @var TransientCache
     */
    private $lockCache;

    /**
     * @param TransientCache $lockCache
     */
    public function let(TransientCache $lockCache): void
    {
        $this->beConstructedWith($lockCache, self::KEY_PREFIX, self::PROCESS_ID);

        $this->lockCache = $lockCache;
    }

    // Tests for: requestLock
    // ======================================================

    public function it_grants_lock_if_you_already_own_it(): void
    {
        $key = 'key';

        $this->simulateGet($key, self::PROCESS_ID);

        $this->requestLock($key)->shouldBe(true);
    }

    public function it_denies_lock_if_someone_else_owns_it(): void
    {
        $key = 'key';
        $otherProcessId = "Definitely not " . self::PROCESS_ID;

        $this->simulateGet($key, $otherProcessId);

        $this->requestLock($key)->shouldBe(false);
    }

    public function it_grants_lock_if_it_is_unlocked(): void
    {
        $key = 'key';

        $this->simulateGet($key, false, self::PROCESS_ID);
        $this->simulateSet($key);

        $this->requestLock($key)->shouldBe(true);
    }

    // Tests for: waitWhileLocked
    // ======================================================

    public function it_does_not_wait_if_there_is_no_lock(): void
    {
        $key = 'key';

        $this->simulateGet($key, null);

        $this->lockCache->get(self::KEY_PREFIX . $key)->shouldBeCalledTimes(1);

        $this->waitWhileLocked($key);
    }

    public function it_waits_while_locked(): void
    {
        $key = 'key';
        $otherProcessId = 'Definitely not ' . self::PROCESS_ID;

        // For the first two iterations, the owner is $otherProcessId. After that it is unlocked.
        $this->simulateGet($key, $otherProcessId, $otherProcessId, null);

        $this->lockCache->get(self::KEY_PREFIX . $key)->shouldBeCalledTimes(3);

        $this->waitWhileLocked($key);
    }

    // Tests for: unlock
    // ======================================================

    public function it_unlocks_if_you_own_the_lock(): void
    {
        $key = 'key';

        $this->simulateGet($key, self::PROCESS_ID);
        $this->lockCache->delete(self::KEY_PREFIX . $key)->shouldBeCalled();

        $this->unlock($key);
    }

    public function it_does_not_unlock_if_someone_else_owns_the_lock(): void
    {
        $key = 'key';
        $otherProcessId = 'Definitely not ' . self::PROCESS_ID;

        $this->simulateGet($key, $otherProcessId);
        $this->lockCache->delete(self::KEY_PREFIX . $key)->shouldNotBeCalled();

        $this->unlock($key);
    }

    public function it_does_not_unlock_if_there_is_nothing_to_unlock(): void
    {
        $key = 'key';

        $this->simulateGet($key, false);
        $this->lockCache->delete(self::KEY_PREFIX . $key)->shouldNotBeCalled();

        $this->unlock($key);
    }

    // Helper functions
    // ======================================================

    private function simulateGet(string $key, ...$results): void
    {
        $this->lockCache->get(self::KEY_PREFIX . $key)->willReturn(...$results);
    }

    private function simulateSet(string $key, string $processId = null): void
    {
        $this->lockCache->set(self::KEY_PREFIX . $key, $processId ?? self::PROCESS_ID, Argument::any())->shouldBeCalled();
    }
}
