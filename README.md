[![GitHub Release](https://img.shields.io/github/v/release/lufiipe/simplevent)](https://github.com/lufiipe/simplevent/releases)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/lufiipe/simplevent/php_run_tests.yml)](https://github.com/lufiipe/simplevent/actions)
[![GitHub License](https://img.shields.io/github/license/lufiipe/simplevent?color=yellow)](LICENSE)

# SimplEvent

Simple PHP event listener library.

With the ability to set a priority for each listener, pause/resume, define the number of times a listener can be triggered.

## Install

```
composer require lufiipe/simplevent
```

## Usage

```php
use LuFiipe\SimplEvent\Event;

Event::on('DummyEvent', function () {
    echo 'The dummy event listener has been triggered! <br>';
});

Event::emit('DummyEvent');
```

Usage with parameter

```php
use LuFiipe\SimplEvent\Event;

Event::on('User.Logged', function ($user) {
    echo sprintf('%s is authenticated! <br>', $user['name']);
});

Event::emit('User.Logged', ['name' => 'John Doe']);
```

with multiple parameters

```php
use LuFiipe\SimplEvent\Event;

Event::on('registered', function ($email, $ip, $device) {
    echo sprintf('"%s" registered from address "%s" with a "%s". <br>', $email, $ip, $device);
});

Event::emit('registered', 'john.doe@mail.net', '127.0.0.1', 'mobile');
```

An event can have multiple listeners. 

```php
use LuFiipe\SimplEvent\Event;

Event::on('User.Registered', function ($user) {
    echo 'Log user informations <br>';
});

Event::on('User.Registered', function ($user) {
    echo 'Send email to user <br>';
});

Event::emit('User.Registered', $user);
```

> #### Notice
> Listeners are triggered in the order of their declaration.

> #### Notice
> The event name must contain letters, numbers, dots, and underscores.

## Priority

Since it is possible to attach multiple listeners to an event, it is also possible to assign a priority to each listener.
More the priority value are higher, the sooner the listener will be called.

```php
use LuFiipe\SimplEvent\Event;
use LuFiipe\SimplEvent\ListenerPriority;

Event::on('event.name', function () {
    echo 'Priority 10 <br>';
}, 10);

Event::on('event.name', function () {
    echo 'Priority Max <br>';
}, ListenerPriority::HIGH);

Event::on('event.name', function () {
    echo 'Priority 20 <br>';
}, 20);

Event::emit('event.name');
```

The above example will output:

```
Priority Max
Priority 20
Priority 10
```

Available priority constants

- `ListenerPriority::MAX` : Max priority
- `ListenerPriority::HIGH` : High priority
- `ListenerPriority::NORMAL` : Normal priority (by default)
- `ListenerPriority::LOW` : Low priority
- `ListenerPriority::MIN` : MIN priority

> #### Notice
> Like closures, priorities cannot be changed once declared.

## Pause/Resume

### Pause

It is possible to pause the listeners

```php
use LuFiipe\SimplEvent\Event;

$listner = Event::on('Comment.post', function ($comment) {
    echo sprintf('Comment posted: "%s" <br>', $comment);
});

Event::emit('Comment.post', 'Foo');

$listner->pause();

Event::emit('Comment.post', 'Bar');
```

The above example will output:

```
Comment posted: "Foo"
```

### Resume

And to resume just use the `resume()` method of your listener.

```php
use LuFiipe\SimplEvent\Event;

$listner = Event::on('Comment.post', function ($comment) {
    echo sprintf('Comment posted: "%s" <br>', $comment);
});

Event::emit('Comment.post', 'Foo');

$listner->pause();

Event::emit('Comment.post', 'Bar');

$listner->resume();

Event::emit('Comment.post', 'Baz');
```

The above example will output:

```
Comment posted: "Foo"
Comment posted: "Baz"
```

## Run the listener multiple times

Specify the number of times the listener can be called.

In this example, the listeners for the "EventX" event will be triggered once.

```php
use LuFiipe\SimplEvent\Event;
use LuFiipe\SimplEvent\ListenerTimes;

Event::on('EventX', function () {
})->setTimes(ListenerTimes::ONCE);
```

In the example below, listeners will be triggered three times.

```php
use LuFiipe\SimplEvent\Event;

Event::on('Event.dummy', function ($number) {
    echo sprintf("triggered %d time(s) <br>", $number);
})->setTimes(3);

for ($i = 1; $i < 10; $i++) {
    Event::emit('Event.dummy', $i);
}
```

The above example will output:

```
triggered 1 time(s)
triggered 2 time(s)
triggered 3 time(s)
```

Available times constants

- `ListenerTimes::ALWAYS` : Always listen (by default)
- `ListenerTimes::NEVER` : Never listen
- `ListenerTimes::ONCE` : Listen one time

## Remove all listeners from an event

```php
Event::unregister('name');
```

## Reset all events

```php
Event::reset();
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
