<?php

namespace LuFiipe\SimplEvent;

/**
 * Event interface
 */
interface EventInterface
{
    /**
     * Listen to an event
     *
     * @param string $name Event name
     * @param callable $callback Listener handler
     * @param int $priority Priority level
     * @return Listener
     */
    public static function on(string $name, callable $callback, int $priority = ListenerPriority::NORMAL): Listener;

    /**
     * Trigger event
     *
     * @param $name Event name
     * @param null $args Arguments
     * @return mixed
     */
    public static function emit(string $name, ...$args);

    /**
     * Remove event
     *
     * @param $name string Event name
     * @return bool
     */
    public static function unregister(string $name): bool;

    /**
     * Reset all listeners
     *
     * @return bool
     */
    public static function reset(): bool;
}
