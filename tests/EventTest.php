<?php

namespace LuFiipe\SimplEvent\Tests;

use LuFiipe\SimplEvent\Event;
use LuFiipe\SimplEvent\InvalidArgumentException;
use LuFiipe\SimplEvent\Listener;
use LuFiipe\SimplEvent\ListenerTimes;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    /**
     * This method is called after each test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        Event::reset();
    }

    /**
     * @dataProvider listenerValidProvider
     *
     * @param string $name
     * @param callable $callback
     * @return void
     */
    public function testAddListener($name, $callback)
    {
        $listener = Event::on($name, $callback);

        $this->assertInstanceOf(Listener::class, $listener);
    }

    /**
     * @dataProvider listenerInvalidProvider
     * 
     * @param string $name
     * @param callable $callback
     * @return void
     */
    public function testAddListenerWithInvalidArgs($name, $callback)
    {
        $this->expectException(InvalidArgumentException::class);

        Event::on($name, $callback);
    }

    public function testTriggerEvent()
    {
        $triggered = false;

        $eventName = 'event.test';

        Event::on($eventName, function ($boolTrue) use (&$triggered) {
            $triggered = $boolTrue;
        });

        Event::emit($eventName, true);

        $this->assertTrue($triggered);
    }

    public function testTriggerEventWithoutArgs()
    {
        $triggered = false;

        $eventName = 'event.test';

        Event::on($eventName, function () use (&$triggered) {
            $triggered = true;
        });

        Event::emit($eventName);

        $this->assertTrue($triggered);
    }


    public function testTriggerEventWithManyArgs()
    {
        $result = [];

        $eventName = 'event.test.args';

        Event::on($eventName, function ($firstName, $name, $email) use (&$result) {
            $result = [
                'name' => $name,
                'first_name' => $firstName,
                'mail' => $email,
            ];
        });

        Event::emit($eventName, 'John', 'Doe', 'john.doe@mail.net');

        $this->assertEquals([
            'name' => 'Doe',
            'first_name' => 'John',
            'mail' => 'john.doe@mail.net',
        ], $result);
    }

    public function testEventPriority()
    {
        $results = [];

        $eventName = 'event.test.priority';

        Event::on($eventName, function () use (&$results) {
            $results[] = 10;
        }, 10);
        Event::on($eventName, function () use (&$results) {
            $results[] = 0;
        }, 0);
        Event::on($eventName, function () use (&$results) {
            $results[] = 100;
        }, 100);
        Event::on($eventName, function () use (&$results) {
            $results[] = 20;
        }, 20);

        Event::emit($eventName);

        $this->assertEquals([100, 20, 10, 0], $results);
    }

    public function testEventPause()
    {
        $results = [];

        $eventName = 'event.test.pause';

        $listner = Event::on($eventName, function ($value) use (&$results) {
            $results[] = $value;
        }, 10);

        Event::emit($eventName, 'One');

        $listner->pause();

        Event::emit($eventName, 'Two');
        Event::emit($eventName, 'Three');
        Event::emit($eventName, '...');

        $listner->resume();

        Event::emit($eventName, 'End');

        $this->assertEquals(['One', 'End'], $results);
    }

    public function testEventTriggeredOneTime()
    {
        $results = [];

        $eventName = 'event.test.times';

        Event::on($eventName, function ($times) use (&$results) {
            $results[] = $times;
        })->setTimes(ListenerTimes::ONCE);

        for ($i = 1; $i <= 5; $i++) {
            Event::emit($eventName, $i);
        }

        $this->assertCount(ListenerTimes::ONCE, $results);
    }

    public function testEventNeverTriggered()
    {
        $results = [];

        $eventName = 'event.test.times';

        Event::on($eventName, function ($times) use (&$results) {
            $results[] = $times;
        })->setTimes(ListenerTimes::NEVER);

        for ($i = 1; $i <= 5; $i++) {
            Event::emit($eventName, $i);
        }

        $this->assertEmpty($results);
    }

    public function testEventTriggeredManyTimes()
    {
        $results = [];

        $eventName = 'event.test.times';
        $many = 3;

        Event::on($eventName, function ($times) use (&$results) {
            $results[] = $times;
        })->setTimes($many);

        for ($i = 1; $i <= $many + 5; $i++) {
            Event::emit($eventName, $i);
        }

        $this->assertCount($many, $results);
    }

    public function testEventTriggeredNegativeTimesThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        Event::on('event.test.times', function () {})->setTimes(-1);

        Event::emit('event.test.times');
    }

    public function testEventUnregister()
    {
        $results = [];

        $eventName = 'event.test.unregister';

        Event::on($eventName, function ($value) use (&$results) {
            $results[] = $value;
        });

        Event::emit($eventName, 'A');

        $this->assertEquals(['A'], $results);

        $isExistingUnregistered = Event::unregister($eventName);

        Event::emit($eventName, 'B');

        $this->assertEquals(['A'], $results);
        $this->assertTrue($isExistingUnregistered);

        $isUnknowUnregistered = Event::unregister('Foo');
        $this->assertFalse($isUnknowUnregistered);
    }

    public function testEventReset()
    {
        $triggered = false;

        $eventName = 'event.test.reset';

        Event::on($eventName, function ($boolTrue) use (&$triggered) {
            $triggered = $boolTrue;
        });

        $isReseted = Event::reset();
        $this->assertTrue($isReseted);

        Event::emit('event.test', true);
        $this->assertFalse($triggered);
    }

    /**
     * DataProvider with valid parameters
     *
     * @return array
     */
    public static function listenerValidProvider(): array
    {
        $fn = function () {};

        return [
            ['name', $fn],
            ['name.action', $fn],
            ['name_action', $fn],
            ['Name', $fn],
            ['Name.Action', $fn],
            ['Name_Action', $fn],
            ['0123456789', $fn],
            [1234567890, $fn],
            ['Name1_Action2.foo3', $fn],
        ];
    }

    /**
     * DataProvider with invalid parameters
     *
     * @return array
     */
    public static function listenerInvalidProvider(): array
    {
        return [
            ['!Name.action_foo1', function () {}],
            ['', function () {}],
        ];
    }
}
