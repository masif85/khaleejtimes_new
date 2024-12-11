<?php

namespace Spec\Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\Contracts\ContentEvent;
use Everyware\Plugin\ContentSync\Contracts\ContentEventListener;
use Everyware\Plugin\ContentSync\EventDispatcher;
use PhpSpec\ObjectBehavior;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class EventDispatcherSpec extends ObjectBehavior
{
    /**
     * @var ListenerProviderInterface
     */
    protected $listenerProvider;

    public function let(ListenerProviderInterface $listenerProvider): void
    {
        $this->listenerProvider = $listenerProvider;

        $listenerProvider->implement(ListenerProviderInterface::class);
        $listenerProvider->beADoubleOf(ListenerProviderInterface::class);

        $this->beConstructedWith($listenerProvider);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(EventDispatcher::class);
        $this->shouldImplement(EventDispatcherInterface::class);
    }

    public function it_should_get_listeners_from_its_provider(ContentEvent $event): void
    {
        $this->listenerProvider->getListenersForEvent($event)
            ->shouldBeCalled()
            ->willReturn([]);

        $this->dispatch($event);

    }

    public function it_should_dispatch_the_event_to_the_registered_listeners(
        ContentEvent $event,
        ContentEventListener $listener,
        ContentEventListener $otherListener
    ): void
    {
        $this->listenerProvider->getListenersForEvent($event)
            ->shouldBeCalled()
            ->willReturn([
                [$listener, 'handle'],
                [$otherListener, 'handle'],
                [$listener, 'handle']
            ]);

        $listener->handle($event)->shouldBeCalledTimes(2);
        $otherListener->handle($event)->shouldBeCalledTimes(1);

        $this->dispatch($event);
    }
}
