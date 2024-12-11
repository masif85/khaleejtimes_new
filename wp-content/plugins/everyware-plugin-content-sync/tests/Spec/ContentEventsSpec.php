<?php

namespace Spec\Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\ContentEvents;
use Everyware\Plugin\ContentSync\Contracts\ContentEvent;
use Everyware\Plugin\ContentSync\Contracts\ContentEventListener;
use PhpSpec\ObjectBehavior;

class ContentEventsSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ContentEvents::class);
    }

    public function it_will_offer_a_way_to_listen_to_events_from_specific_content_type(
        ContentEventListener $imageListener,
        ContentEventListener $articleListenerOne,
        ContentEventListener $articleListenerTwo,
        ContentEvent $articleEvent,
        ContentEvent $imageEvent
    ): void
    {
        $articleEvent->getContentType()->willReturn('Article');
        $imageEvent->getContentType()->willReturn('Image');

        $articleListenerOne->handle($articleEvent)->shouldBeCalledTimes(1);
        $articleListenerTwo->handle($articleEvent)->shouldBeCalledTimes(1);
        $imageListener->handle($imageEvent)->shouldBeCalledTimes(1);

        $this->listen('Article', $articleListenerOne);
        $this->listen('Image', $imageListener);
        $this->listen('Article', $articleListenerTwo);

        $this->dispatch($articleEvent);
        $this->dispatch($imageEvent);
    }

    public function it_will_not_register_duplicate_listeners_on_the_same_type(
        ContentEventListener $listener,
        ContentEvent $articleEvent,
        ContentEvent $imageEvent
    ): void
    {
        $articleEvent->getContentType()->willReturn('Article');
        $imageEvent->getContentType()->willReturn('Image');

        $listener->handle($articleEvent)->shouldBeCalledTimes(1);
        $listener->handle($imageEvent)->shouldBeCalledTimes(1);

        $this->listen('Article', $listener);
        $this->listen('Image', $listener);
        $this->listen('Article', $listener);

        $this->dispatch($articleEvent);
        $this->dispatch($imageEvent);
    }

    public function it_will_offer_a_way_to_listen_to_events_from_all_content_types(
        ContentEventListener $imageListener,
        ContentEventListener $articleListenerOne,
        ContentEventListener $articleListenerTwo,
        ContentEvent $articleEvent,
        ContentEvent $imageEvent
    ): void
    {
        $articleEvent->getContentType()->willReturn('Article');

        $articleListenerOne->handle($articleEvent)->shouldBeCalledTimes(2);
        $articleListenerTwo->handle($articleEvent)->shouldBeCalledTimes(1);

        $this->listen(ContentEvents::ALL_TYPES, $articleListenerOne);
        $this->listen('Article', $articleListenerOne);
        $this->listen(ContentEvents::ALL_TYPES, $articleListenerTwo);


        $this->dispatch($articleEvent);
    }
}
