<?php

namespace Spec\Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\Contracts\ContentEvent;
use Everyware\Plugin\ContentSync\Contracts\ContentEventListener;
use Everyware\Plugin\ContentSync\ListenerProvider;
use PhpSpec\ObjectBehavior;

class ListenerProviderSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ListenerProvider::class);
    }

    public function it_should_store_and_retrieve_listeners_based_on_the_events(
        ContentEvent $event,
        ContentEventListener $listener
    ): void {
        $this->addListener(ContentEvent::class, static function ($event) {
            return $event;
        }); // With closure

        $this->addListener(ContentEvent::class, [$listener, 'handle']); // With method from instance
        $this->addListener(ContentEvent::class, 'is_callable'); // With public function

        $this->getListenersForEvent($event)->shouldHaveCount(3);
    }

    public function it_should_store_the_events_in_a_prioritized_order(ContentEvent $event): void
    {
        $callables = [
            'array_chunk',
            'array_column',
            'array_combine',
            'array_diff',
            'array_fill'
        ];

        $this->addListener(ContentEvent::class, $callables[4]);
        $this->addListener(ContentEvent::class, $callables[3], 4);
        $this->addListener(ContentEvent::class, $callables[2], 3);
        $this->addListener(ContentEvent::class, $callables[0], 1);
        $this->addListener(ContentEvent::class, $callables[1], 2);

        $this->getListenersForEvent($event)->shouldHaveCount(5);
        $this->getListenersForEvent($event)->shouldIterateLike($callables);
    }

    public function it_can_store_events_using_string_name(
        ContentEvent $event,
        ContentEventListener $listener
    ): void {
        $this->addListener('some_event', static function ($event) {
            return $event;
        }); // With closure

        $this->addListener('some_other_event', [$listener, 'handle']); // With method from instance
        $this->addListener('some_other_event', 'is_callable'); // With public function

        $this->addListener(ContentEvent::class, [$listener, 'handle']); // With method from instance
        $this->addListener(ContentEvent::class, 'is_callable'); // With public function

        $this->getListenersForEventName('some_event')->shouldHaveCount(1);

        $this->getListenersForEventName('some_other_event')->shouldHaveCount(2);

        $this->getListenersForEventName(ContentEvent::class)->shouldHaveCount(2);

        $this->getListenersForEvent($event)->shouldHaveCount(2);
    }
}
