<?php

namespace Everyware\Plugin\ContentSync;

use Psr\EventDispatcher\ListenerProviderInterface;

class ListenerProvider implements ListenerProviderInterface
{
    /**
     * Prioritized list of callable listeners.
     *
     * Each priority will have a set of registered "events" where the callable listeners will be added.
     *
     * @var array $listeners [string priority => [string eventName => callable[], ...], ...]
     */
    private array $listeners = [];

    public function getListenersForEvent(object $event): iterable
    {
        foreach ($this->getListenersInAscendingOrder() as $eventName => $listeners) {
            if ($event instanceof $eventName) {
                foreach ($listeners as $listener) {
                    yield $listener;
                }
            }
        }
    }

    public function getListenersForEventName(string $name): iterable
    {
        foreach ($this->getListenersInAscendingOrder() as $eventName => $listeners) {
            if ($name === $eventName) {
                foreach ($listeners as $listener) {
                    yield $listener;
                }
            }
        }
    }

    public function addListener(string $eventName, callable $listener, int $priority = 10): void
    {
        $priorityIndex = sprintf('%d.0', $priority);
        if (isset($this->listeners[$priorityIndex][$eventName]) &&
            in_array($listener, $this->listeners[$priorityIndex][$eventName], true)
        ) {
            // Duplicate detected
            return;
        }
        $this->listeners[$priorityIndex][$eventName][] = $listener;
    }

    /**
     * @return iterable [string eventName => callable[], ...] All listeners grouped by the event name.
     */
    protected function getListenersInAscendingOrder(): iterable
    {
        $priorities = $this->getPrioritiesInAscendingOrder();

        foreach ($priorities as $priority) {
            foreach ($this->listeners[$priority] as $eventName => $listeners) {
                yield $eventName => $listeners;
            }
        }
    }

    protected function getPrioritiesInAscendingOrder(): array
    {
        $priorities = array_keys($this->listeners);
        usort($priorities, static function ($a, $b) {
            return $a <=> $b;
        });

        return $priorities;
    }
}
