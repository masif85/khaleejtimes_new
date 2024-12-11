<?php declare(strict_types=1);

namespace Everyware\Concepts\Wordpress\Contracts;

/**
 * Interface WpAction
 * @package Everyware\Concepts\Wordpress\Contracts
 */
interface WpAction
{
    /**
     * @see   https://developer.wordpress.org/reference/functions/add_action/
     *
     * @param string   $tag
     * @param callable $function_to_add
     * @param int      $priority
     * @param int      $accepted_args
     *
     * @return bool
     */
    public function addAction(string $tag, $function_to_add, int $priority = 10, int $accepted_args = 1): bool;

    /**
     * @see   https://developer.wordpress.org/reference/functions/current_action/
     *
     * @return string
     */
    public function currentAction(): string;

    /**
     * @see   https://developer.wordpress.org/reference/functions/did_action/
     *
     * @param string $tag
     *
     * @return int
     */
    public function didAction(string $tag): int;

    /**
     * @see   https://developer.wordpress.org/reference/functions/do_action/
     *
     * @param string $tag
     * @param mixed  $arg,...
     *
     * @return void
     */
    public function doAction(string $tag, $arg = ''): void;

    /**
     * @see   https://developer.wordpress.org/reference/functions/do_action_ref_array/
     *
     * @param string $tag
     * @param array  $args
     *
     * @return void
     */
    public function doActionRefArray(string $tag, array $args): void;

    /**
     * @see   https://developer.wordpress.org/reference/functions/doing_action/
     *
     * @param string|null $action
     *
     * @return bool
     */
    public function doingAction($action = null): bool;

    /**
     * Gets the actions of a specific tag or all actions sorted by descending priority.
     *
     * @param string|null $tag The name of the tag
     *
     * @return array The event listeners for the specified event, or all event listeners by event name
     */
    public function getActions($tag = null): array;

    /**
     * @see   https://developer.wordpress.org/reference/functions/has_action/
     *
     * @param string        $tag
     * @param callable|bool $function_to_check
     *
     * @return bool|int
     */
    public function hasAction(string $tag, $function_to_check = false);

    /**
     * @see   https://developer.wordpress.org/reference/functions/remove_action/
     *
     * @param string   $tag
     * @param callable $function_to_remove
     * @param int      $priority
     *
     * @return bool
     */
    public function removeAction(string $tag, $function_to_remove, $priority = 10): bool;

    /**
     * @see   https://developer.wordpress.org/reference/functions/remove_all_actions/
     *
     * @param string   $tag
     * @param int|bool $priority
     *
     * @return true
     */
    public function removeAllActions(string $tag, $priority = false): bool;
}
