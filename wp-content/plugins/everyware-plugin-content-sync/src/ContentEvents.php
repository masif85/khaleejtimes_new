<?php

namespace Everyware\Plugin\ContentSync;

use Everyware\Plugin\ContentSync\Contracts\ContentEvent;
use Everyware\Plugin\ContentSync\Contracts\ContentEventListener;

class ContentEvents
{
    public const ALL_TYPES = 'all';

    private static array $listenerProviders = [];

    public static function listen(string $contenttype, ContentEventListener $listener, int $priority = 10): void
    {
        $listenerProvider = static::$listenerProviders[$contenttype] ?? new ListenerProvider();

        $listenerProvider->addListener(ContentEvent::class, [$listener, 'handle'], $priority);

        static::$listenerProviders[$contenttype] = $listenerProvider;
    }

    public static function dispatch(ContentEvent $event): void
    {
        // Fire event for all types
        if (isset(static::$listenerProviders[static::ALL_TYPES])) {
            (new EventDispatcher(static::$listenerProviders[static::ALL_TYPES]))->dispatch($event);
        }

        $contenttype = $event->getContentType();

        // Fire event for specific type
        if (isset(static::$listenerProviders[$contenttype])) {
            (new EventDispatcher(static::$listenerProviders[$contenttype]))->dispatch($event);
        }
    }
}
