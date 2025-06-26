<?php

namespace LuFiipe\SimplEvent;

/**
 * Event
 */
class Event implements EventInterface
{
    /**
     * Listeners
     *
     * @var Listener[][]
     */
    private static $listeners = [];

    /**
     * Listen to an event
     *
     * @param string $name Event name
     * @param callable $callback Listener handler
     * @param int $priority Priority level
     * @throws InvalidArgumentException
     * @return Listener
     */
    public static function on(string $name, callable $callback, int $priority = ListenerPriority::NORMAL): Listener
    {
        $name = self::sanitizeEventName($name);

        if (!self::eventExists($name)) {
            self::$listeners[$name] = [];
        }

        $listener = new Listener($callback, (int) $priority);

        self::$listeners[$name][] = $listener;

        self::prioritize($name);

        return $listener;
    }

    /**
     * Trigger event
     *
     * @param string $name Event name
     * @param mixed ...$args Arguments
     * @return mixed
     */
    public static function emit($name, ...$args)
    {
        $name = self::sanitizeEventName($name);

        if (self::eventExists($name)) {
            foreach (self::$listeners[$name] as $listener) {

                if ($listener->isOnPause()) {
                    break;
                }

                $listener->handle($args);
            }
        }
    }

    /**
     * Remove event
     *
     * @param string $name Event name
     * @return bool
     */
    public static function unregister($name): bool
    {
        $name = self::sanitizeEventName($name);

        if (self::eventExists($name)) {
            unset(self::$listeners[$name]);

            return true;
        }

        return false;
    }

    /**
     * Reset all listeners
     *
     * @return bool
     */
    public static function reset(): bool
    {
        self::$listeners = [];

        return true;
    }

    /**
     * Checks if the given event name exists
     *
     * @param string $name Event name
     * @return boolean
     */
    private static function eventExists(string $name): bool
    {
        return array_key_exists($name, self::$listeners);
    }

    /**
     * Return the cleaned name of the event
     *
     * @param string $name Event name
     * @throws InvalidArgumentException
     * @return string
     */
    private static function sanitizeEventName($name): string
    {
        $name = (string) $name;

        if ((bool) preg_match('/^[a-zA-Z0-9._]+$/', $name) == false) {
            throw new InvalidArgumentException('The event name must contain letters, numbers, dots, and underscores');
        }

        return $name;
    }

    /**
     * Prioritize listeners 
     *
     * @param string $name Event name
     * @return void
     */
    private static function prioritize(string $name): void
    {
        uasort(self::$listeners[$name], function (Listener $a, Listener $b) {
            return $b->getPriority() <=> $a->getPriority();
        });
    }
}
