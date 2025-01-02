<?php

namespace LuFiipe\SimplEvent;

/**
 * Priority constants
 */
final class ListenerPriority
{
    /**
     * Maximum priority.
     *
     * @const int
     */
    public const MAX = PHP_INT_MAX;

    /**
     * High priority.
     *
     * @const int
     */
    public const HIGH = 100;

    /**
     * Normal priority.
     *
     * @const int
     */
    public const NORMAL = 0;

    /**
     * Low priority.
     *
     * @const int
     */
    public const LOW = -100;

    /**
     * Minimum priority.
     *
     * @const int
     */
    public const MIN = PHP_INT_MIN;
}
