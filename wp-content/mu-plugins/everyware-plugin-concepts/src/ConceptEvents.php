<?php declare(strict_types=1);

namespace Everyware\Concepts;

use Everyware\Concepts\Contracts\ConceptEvent;
use Everyware\Concepts\Wordpress\Action;
use Everyware\Concepts\Wordpress\Contracts\WpAction;
use Infomaker\Everyware\Support\Str;

/**
 * Class ConceptEvents
 * @package Everyware\Concepts
 */
class ConceptEvents
{
    private const ACTION_PREFIX = 'ew_concept';

    /**
     * @var WpAction
     */
    private $action;

    public function __construct(WpAction $action)
    {
        $this->action = $action;
    }

    /**
     * @param string   $eventName
     * @param callable $callable
     * @param int      $priority
     */
    public function addListener(string $eventName, $callable, $priority = 0): void
    {
        $callback = $callable;

        // Try to parse strings that are not callable like "Some\Class@method"
        if (is_string($callable) && ! is_callable($callable)) {
            $callback = Str::parseCallback($callable, 'handle');
        }

        $this->action->addAction(static::ACTION_PREFIX . "_{$eventName}", $callback, $priority);
    }

    public function triggerEvent(string $eventName, ConceptEvent $callable): void
    {
        $this->action->doAction(static::ACTION_PREFIX . "_{$eventName}", $callable);
    }

    /**
     * @param string   $eventName
     * @param callable $callable
     * @param int      $priority
     */
    public static function listen(string $eventName, $callable, $priority = 0): void
    {
        $events = new static(new Action());

        $events->addListener($eventName, $callable, $priority);
    }

    /**
     * @param string       $eventName
     * @param ConceptEvent $event
     */
    public static function fire(string $eventName, ConceptEvent $event): void
    {
        $events = new static(new Action());

        $events->triggerEvent($eventName, $event);
    }

    /**
     * @param callable $callable
     * @param int      $priority
     */
    public static function onCreate($callable, $priority = 0): void
    {
        static::listen('created', $callable, $priority);
    }

    /**
     * @param callable $callable
     * @param int      $priority
     */
    public static function onUpdate($callable, $priority = 0): void
    {
        static::listen('updated', $callable, $priority);
    }

    /**
     * @param callable $callable
     * @param int      $priority
     */
    public static function onDelete($callable, $priority = 0): void
    {
        static::listen('deleted', $callable, $priority);
    }
}
