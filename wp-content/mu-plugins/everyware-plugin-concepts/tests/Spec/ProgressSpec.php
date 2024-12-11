<?php

namespace Spec\Everyware\Concepts;

use Everyware\Concepts\Contracts\SimpleCacheHandler;
use Everyware\Concepts\Progress;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProgressSpec extends ObjectBehavior
{
    /**
     * @var int
     */
    private $maxTime;

    public function let(SimpleCacheHandler $cache): void
    {
        $this->maxTime = 60 * 60;
        $this->beConstructedWith($cache);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(Progress::class);
    }

    public function it_can_generate_a_key_specific_to_the_progress()
    {
        $key = 'key';
        $this->generateKey($key)->shouldReturn(md5('concept_processing_' . $key, true));
    }

    public function it_can_start_progress_using_a_specific_key(SimpleCacheHandler $cache)
    {
        $key = 'key';
        $progressKey = $this->generateKey($key);

        $cache->set($progressKey, Progress::PROCESSING, $this->maxTime)->shouldBeCalled();

        $this->start($key);
    }

    public function it_should_know_if_a_progress_is_running(SimpleCacheHandler $cache)
    {
        $key = 'key';

        $cache->get($this->generateKey($key))->willReturn(Progress::PROCESSING);

        $this->inProgress($key)->shouldReturn(true);
    }

    public function it_should_know_if_a_progress_is_not_running(SimpleCacheHandler $cache)
    {
        $key = 'key';

        $cache->get($this->generateKey($key))->willReturn(false);

        $this->inProgress($key)->shouldReturn(false);
    }

    public function it_can_stop_an_existing_progress(SimpleCacheHandler $cache)
    {
        $key = 'key';

        $cache->delete($this->generateKey($key))->shouldBeCalled();

        $this->stop($key);
    }
}
