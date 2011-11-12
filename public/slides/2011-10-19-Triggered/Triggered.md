Triggered!
==========

---

Terminology
------

* An *Event* is an _action_
* A *Listener* is a callback that responds to an _event_
* An *EventManager* is a collection of _listeners_ and _triggers_ events

---

In a Nutshell
----

    !php
    <?php
    use Zend\EventManager\EventManager;
    
    $events = new EventManager();
    
    $events->attach('do', function($e) {
        $event  = $e->getName();
        $params = $e->getParams();
        printf(
            'Handled event "%s", with parameters %s',
            $event,
            json_encode($params)
        );
    });
    
    $params = array('foo' => 'bar', 'baz' => 'bat');
    $events->trigger('do', null, $params);

---

Attach
----

.notes: Priority allows doing pre/post style hooks

* An event _name_ 
* The callback that will respond
* Optionally, the priority at which to listen

---

    !php
    <?php
    $events->attach('do', $callback);       // base priority
    $events->attach('do', $callback, 100);  // high priority
    $events->attach('do', $callback, -100); // low priority

---

Trigger
----

Typically:

* Event _name_
* Target (object or function triggering the event)
* Arguments

---

    !php
    <?php
    // instance method
    public function foo($bar, $baz)
    {
        $params = compact('bar', 'baz');
        $this->events()->trigger('do', $this, $params);
    }

---

Event
----

    !php
    <?php
    interface EventDescription
    {
        public function setName($name);
        public function setTarget($target);
        public function setParams($params);
        public function getName();
        public function getTarget();
        public function getParam($name, $default = null);
        public function getParams();
    }

---

Listeners
----

Listeners receive an _Event_ as their sole argument.

Listeners can be _any_ valid PHP callback.

---

    !php
    <?php
    function ($e) {
        $event  = $e->getName();
        $target = $e->getTarget();
        $target = (is_object($target) 
                ? get_class($target) 
                : gettype($target))
        $params = $e->getParams();
        printf(
            'Event "%s", params %s, target "%s"',
            $event,
            json_encode($params),
            $target
        );
    }

---

Digression: Custom Events
==========

---


Semantics matter.
=========

---

An Example of Bad Semantics
----

    !php
    <?php
    $callback = function($e) {
        $result  = $e->getParam('__RESULT__');
        $matches = $e->getParam('route-match');
        // ...
    };

---

Better Semantics
----

    !php
    <?php
    $callback = function($e) {
        $result  = $e->getResult();
        $matches = $e->getRouteMatch();
        // ...
    };

---

Creating Custom Events
----

.notes: Using built-in parameters makes martialling easier.

Easiest is to simply extend Zend&#92;EventManager&#92;Event:

    !php
    <?php
    use Zend\EventManager\Event;

    class MyEvent extends Event
    {
        protected $result;

        public function setResult($result)
        {
            $this->setParam('result', $result);
        }

        public function getResult()
        {
            return $this->getParam('result');
        }
    }

---

Using Custom Events: part 1
----

Pass as sole parameter:

    !php
    <?php
    $event = new MyEvent();
    $event->setName('foo');
    $event->setTarget($this);

    $events->trigger($event);

---

Using Custom Events: part 2
----

Pass as second parameter:

    !php
    <?php
    $event = new MyEvent();
    $event->setTarget($this);

    $events->trigger('foo', $event);

---

Using Custom Events: part 3
----

Pass as third parameter:

    !php
    <?php
    $event = new MyEvent();

    $events->trigger('foo', $this, $event);

---

Recommended Usage
===========

---

Compose an EventManager
----

.notes: This would make a great Trait

    !php
    <?php
    use Zend\EventManager\EventCollection,
        Zend\EventManager\EventManager;
    class Foo
    {
        protected $events;
        public function setEventManager(EventCollection $events)
        {
            $this->events = $events;
        }
        public function events()
        {
            if (!$this->events) {
                $this->setEventManager(new EventManager(array(
                    __CLASS__, get_called_class(),
                ));
            }
            return $this->events;
        }
    }

---

Trigger Events
----

    !php
    <?php
    class Foo
    {
        // ...
        
        public function bar($baz, $bat = null)
        {
            $params = compact($baz, $bat);
            $this->events()->trigger(__FUNCTION__, $this, $params);
        }
    }

---

Notes on Triggering
----

* Use the method name as the event.
    * If triggering more than one event, use dot-notation (e.g., "dispatch.pre",
      "dispatch.post").
* Pass all input to the method as parameters.

---

Aggregate Listeners
===========

---

.notes: Examples might be composing object collaborators

Sometimes you'll want stateful listeners.

---

.notes: Example includes view listener in ZF2 - views, layouts, error pages

Sometimes several listeners fall under the same area of responsibility.

---

Aggregate Listeners
-----------

* Allow attaching and detaching many listeners at once
* Pass an aggregate to the event manager, or vice versa

---

Example
----

    !php
    <?php
    class LogEvents implements ListenerAggregate
    {
        protected $handlers = array();
        protected $log;

        public function __construct(Logger $log)
        {
            $this->log = $log;
        }

        public function log(Event $e)
        {
            $event  = $e->getName();
            $params = $e->getParams();
            $log->info(sprintf('%s: %s', $event, json_encode($params)));
        }

        // ...
    }

---

Aggregate Functionality
-------

    !php
    <?php
    public function attach(EventCollection $events)
    {
        $this->handlers[] = $events->attach('do', array($this, 'log'));
        $this->handlers[] = $events->attach('doSomethingElse', array($this, 'log'));
    }

    public function detach(EventCollection $events)
    {
        foreach ($this->handlers as $key => $handler) {
            $events->detach($handler);
            unset($this->handlers[$key];
        }
        $this->handlers = array();
    }

---

Using Aggregates
----------

    !php
    <?php
    $logEvents = new LogEvents($logger);
    $events->attach($logEvents);

    // or
    $logEvents->attach($events);

---

Attaching Listeners Globally
============

---

Why?
----

* Listeners are often used for _cross-cutting concerns_.
* Often, you'll want to setup listeners early, before you have access to
  specific event manager instances.
* E.g., logging, debugging, caching.

---

How?
----

Use the StaticEventManager.

    !php
    <?php
    use Zend\EventManager\StaticEventManager;
    $events = StaticEventManager::getInstance();
    $events->attach($context, $event, $callback);

---

What is the "context"?
----

* Each EventManager instance can listen on one or more contexts
    * Provided to the constructor
    * Alternately added using `setIdentifier()`
* When triggering events, checks to see if any listeners were registered for its
  contexts with the StaticEventManager
* You can disable this at any time!

---

Example
----

    !php
    <?php
    use Zend\EventManager\StaticEventManager;
    $events = StaticEventManager::getInstance();
    $events->attach('Foo', 'bar', function ($e) {
        $event  = $e->getName();
        $target = $e->getTarget();
        $params = json_encode($e->getParams());
        sprintf('%s::%s: %s', $event, $target, $params);
    });

---

Advanced Topics
=======

---

Short Circuting
====

---

What?
----

* Sometimes you may want to return early based on what a listener does
* Sometimes a listener may want to prevent other listeners from processing

---

How?
----

*Method 1:* Attach a callback when triggering

    !php
    <?php
    $results = $events->trigger($event, function($r) {
        return ($r instanceof Response);
    });

---

How?
----

.notes: This is like JavaScript

*Method 2:* Signal from a listener

    !php
    <?php
    $listener = function ($e) {
        $e->stopPropagation(true);
    };

---

Testing for stopped propagation
----

    !php
    <?php
    $results = $events->trigger(/* ... */);
    if ($results->stopped()) {
        return $results->last();
    }

---

Introspecting results
======

---

Iterate:
---

    !php
    <?php
    foreach ($results as $result) {
        // ...
    }

---

First result:
---

    !php
    <?php
    $first = $results->first();

---

Last result:
---

    !php
    <?php
    $first = $results->last();

---

Test for specific value:
----

    !php
    <?php
    if ($results->contains('foo')) {
        // found!
    }

---

Prioritized Events
=====

---

Why?
----

.notes: Examples: ACLs, caching

Sometimes events need to execute in a given order.

---

How?
----

* Optional final argument to attach() (even statically)
* Default priority is 1
* Higher values == higher priority
* Negative values == lower priority

---

Examples
----

    !php
    <?php
    // High priority:
    $events->attach('foo', $listener, 100);

    // Low priority:
    $events->attach('foo', $listener, -100);

---

Putting it Together
============

---

Our target
----

    !php
    <?php
    public function someExpensiveCall($criteria1, $criteria2)
    {
        $params  = compact('criteria1', 'criteria2');
        $results = $this->events()->trigger(
            __FUNCTION__ . '.pre', $this, $params, function ($r) {
            return ($r instanceof SomeResultClass);
        });
        if ($results->stopped()) {
            return $results->last();
        }
        
        // ... do some work ...
        
        $params['__RESULT__'] = $calculatedResult;
        $this->events()->trigger(
            __FUNCTION__ . '.post', $this, $params);
        return $calculatedResult;
    }

---

Caching: check for match
----

    !php
    <?php
    $events->attach('someExpensiveCall.pre', function($e) use ($cache) {
        $params = $e->getParams();
        $key    = md5(json_encode($params));
        if (false !== ($hit = $cache->load($key)) {
            return $hit;
        }
    }, 100);

---

Caching: cache a value
----

    !php
    <?php
    $events->attach('someExpensiveCall.post', function($e) use ($cache) {
        $params = $e->getParams();
        $result = $params['__RESULT__'];
        unset($params['__RESULT__']);
        $key    = md5(json_encode($params));
        $cache->save($result, $key);
    }, -100);

---

Thank you!
=====

* http://joind.in/3788
* http://twitter.com/weierophinney
* http://framework.zend.com/zf2

