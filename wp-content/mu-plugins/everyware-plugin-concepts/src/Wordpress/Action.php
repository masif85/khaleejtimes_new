<?php declare(strict_types=1);

namespace Everyware\Concepts\Wordpress;

use Everyware\Concepts\Wordpress\Contracts\WpAction;

/**
 * Class Action
 * @package Everyware\Concepts\Wordpress
 */
class Action implements WpAction
{
    /**
     * @inheritDoc
     */
    public function addAction(string $tag, $function_to_add, int $priority = 10, int $accepted_args = 1): bool
    {
        return add_action($tag, $function_to_add, $priority, $accepted_args);
    }

    /**
     * @inheritDoc
     */
    public function currentAction(): string
    {
        return current_action();
    }

    /**
     * @inheritDoc
     */
    public function didAction(string $tag): int
    {
        return did_action($tag);
    }

    /**
     * @inheritDoc
     */
    public function doAction(string $tag, $arg = ''): void
    {
        do_action($tag, $arg);
    }

    /**
     * @inheritDoc
     */
    public function doActionRefArray(string $tag, array $args): void
    {
        do_action_ref_array($tag, $args);
    }

    /**
     * @inheritDoc
     */
    public function doingAction($action = null): bool
    {
        return doing_action($action);
    }

    /**
     * @inheritDoc
     */
    public function getActions($tag = null): array
    {
        $actions = $GLOBALS['wp_filter'] ?? [];

        if ($tag === null) {
            return $actions;
        }

        return $actions[$tag] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function hasAction(string $tag, $function_to_check = false)
    {
        return has_action($tag, $function_to_check);
    }

    /**
     * @inheritDoc
     */
    public function removeAction(string $tag, $function_to_remove, $priority = 10): bool
    {
        return remove_action($tag, $function_to_remove, $priority);
    }

    /**
     * @inheritDoc
     */
    public function removeAllActions(string $tag, $priority = false): bool
    {
        return remove_all_actions($tag, $priority);
    }
}
