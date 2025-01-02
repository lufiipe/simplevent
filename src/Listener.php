<?php

namespace LuFiipe\SimplEvent;

/**
 * Listener
 */
class Listener
{
    /**
     * Listener handler
     *
     * @var callable
     */
    private $callback;

    /**
     * Priority level
     *
     * @var integer
     */
    private int $priority;


    /**
     * Set the number of times the listener can be called
     *
     * @var integer|null
     */
    private ?int $times = ListenerTimes::ALWAYS;

    /**
     * Should parent events be paused
     *
     * @var boolean
     */
    private bool $isOnPause = false;

    /**
     * Number of times the listener can be called
     *
     * @var integer
     */
    private int $calls = 0;


    /**
     *
     * @param callable $callback
     * @param int $priority
     */
    public function __construct(callable $callback, int $priority = ListenerPriority::NORMAL)
    {
        $this->callback = $callback;
        $this->priority = $priority;
    }

    /**
     * Returns the listener handler
     *
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * Returns the priority level
     *
     * @return integer
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Returns the number of times the listener can be called
     *
     * @return integer|null
     */
    public function getTimes(): ?int
    {
        return $this->times;
    }

    /**
     * Sets the number of times the listener can be called
     *
     * @param integer|null $times
     * @return self
     */
    public function setTimes(?int $times): self
    {
        if ($times < 0) {
            throw new InvalidArgumentException('"times" parameter must be positive');
        }

        $this->times = $times;

        return $this;
    }

    /**
     * Returns true if the listener is paused
     *
     * @return boolean
     */
    public function isOnPause(): bool
    {
        return $this->isOnPause;
    }

    /**
     * Pause listener
     *
     * @return self
     */
    public function pause(): self
    {
        $this->isOnPause = true;

        return $this;
    }

    /**
     * Resume listener
     *
     * @return self
     */
    public function resume(): self
    {
        $this->isOnPause = false;

        return $this;
    }

    /**
     * Call the listener handler
     *
     * @param array $args
     * @return mixed
     */
    public function handle(array $args)
    {
        if ($this->times !== ListenerTimes::ALWAYS) {
            if ($this->calls >= $this->times) {
                return null;
            }
        }

        $this->calls++;

        call_user_func_array($this->callback, $args);
    }
}
