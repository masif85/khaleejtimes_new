<?php

namespace Spec\Everyware\Concepts;

use Everyware\Concepts\ConceptEvents;
use Everyware\Concepts\Contracts\ConceptEvent;
use Everyware\Concepts\Wordpress\Contracts\WpAction;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @method addListener(string $string, $callable, $priority)
 */
class ConceptEventsSpec extends ObjectBehavior
{
    /**
     * @var WpAction
     */
    private $action;

    public function let(WpAction $action): void
    {
        $this->beConstructedWith($action);
        $this->action = $action;
    }

    public function it_can_add_listeners_for_closures()
    {
        $callable = function () {
            // do stuff
        };

        $this->assertListenerIsAdded('created', $callable, 0);

        $this->addListener('created', $callable);
    }

    public function it_can_add_listeners_for_callable_array()
    {
        $callable = [static::class, 'let'];

        $this->assertListenerIsAdded('created', $callable, 0);

        $this->addListener('created', $callable);
    }

    public function it_can_add_listeners_for_callable_string()
    {
        $callable = static::class . '::let';

        $this->assertListenerIsAdded('created', $callable, 0);

        $this->addListener('created', $callable);
    }

    public function it_can_add_listeners_for_callable_special_format_string()
    {
        $callable = static::class . '@let';

        $this->assertListenerIsAdded('created', [static::class, 'let'], 0);

        $this->addListener('created', $callable);
    }

    public function it_can_add_listeners_for_callable_class_and_default_to_handle_method()
    {
        $callable = static::class;

        $this->assertListenerIsAdded('created', [static::class, 'handle'], 0);

        $this->addListener('created', $callable);
    }

    public function it_can_add_listeners_with_priority()
    {
        $callable = [static::class, 'let'];
        $priority = 4;

        $this->assertListenerIsAdded('created', $callable, $priority);

        $this->addListener('created', $callable, $priority);
    }

    private function assertListenerIsAdded(string $eventName, $callable, $priority = 0)
    {
        $this->action->addAction("ew_concept_{$eventName}", $callable, $priority)->shouldBeCalled();
    }

    private function assertEventIsTriggered(string $eventName, ConceptEvent $event)
    {
        $this->action->doAction("ew_concept_{$eventName}", $event)->shouldBeCalled();
    }
}
