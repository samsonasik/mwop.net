Zend Framework 2
==========

.fx: titleslide

---

Our goal
----

.fx: centeredHeaders

Better consistency and performance.
----

---

How we'll get there
----

1. **Community**

Presenter Notes
----

Community has helped us identify the problems of ZF1, and also proposed and
provided solutions we're now using and incorporating into ZF2. We've had close
to 300 people make direct commits to ZF1, and close to 100 collaborators on ZF2
already. Our direction is now being driven by community proposals, IRC meetings,
and voting.

---

How we'll get there
----

1. Community
1. **Emphasis on SOLID principles**

Presenter Notes
----

SOLID == **S** ingle-responsibility principle, **O** pen/closed principle, **L** iskov substitution principle, **I** nterface segregation principle, **D** ependency inversion principle. 

In sum, interfaces rule, DI becomes important, and we introduce aspect-oriented
programming principles, via the EventManager.

---

How we'll get there
----

1. Community
1. Emphasis on SOLID principles
1. **Embrace the new features of PHP 5.3+**

Presenter Notes
----

We're using namespaces, autoloading, closures, late static binding, functors,
all the yumminess of SPL, even goto

---

How we'll get there
----

1. Community
1. Emphasis on SOLID principles
1. Utilize PHP 5.3+ to full advantage
1. **Fully utilize HTTP**

Presenter Notes
----

The HTTP specification is full of goodies, and we leverage it and code to it
with a redesigned HTTP component. This includes full encapsulation of HTTP
methods, headers, cookies, and more, and becomes the core of our new MVC layer.

---

A New Core
====

.fx: sectionslide

---

The ZF1 Way
----

.fx: centeredHeaders

Singletons, Registries, and Hard-Coded Dependencies

Presenter Notes
----

The main entry point to a ZF1 app was a singleton, Zend_Controller_Front.
Registries have long been promoted as a way to access dependencies, and are the
primary way i18n/l10n work. Controllers typically instantiated objects they
needed directly. It's a mess.

---

The ZF2 Way
----

.fx: centeredHeaders

Aspect Oriented Design and Dependency Injection

Presenter Notes
----

The two biggest questions as we've worked on ZF2 have centered around how
controllers could get their dependencies, and how to make systems flexible.
Tasks like logging, caching, and authorization should be simple to initiate.

---

Dependency Injection
====

---

Basic Dependency Injection
----

Dependency Injection is not magic.

    !php
    <?php
    class Bar {}

    class Foo
    {
        protected $bar;

        public function __construct(Bar $bar)
        {
            $this->bar = $bar;
        }
    }

---

Setter Injection
----

Sometimes we use setters.

    !php
    <?php
    class Foo
    {
        protected $bar;

        public function setBar(Bar $bar)
        {
            $this->bar = $bar;
        }
    }

---

It solves a problem
----

* How do we enforce a separation of concerns?

---

It creates a new problem
----

* How and when do we create and inject dependencies?

---

DI Containers
----

* Store object definitions, including dependency information
* Store environment-specific configuration of the dependencies
* Handle the creation of objects and their dependencies

---

Sample Definition
----

    !php
    <?php
    $definition = array(
        'Foo' => array(
            'setBar' => array(
                'bar' => array(
                    'type'     => 'Bar',
                    'required' => true,
                ),
            ),
        ),
    );

---

Using the DI Container
----

    !php
    <?php
    use Zend\Di\Di,
        Zend\Di\Configuration;

    $di     = new Di;
    $config = new Configuration(array(
        'definition' => array('class' => $definition)
    ));
    $config->configure($di);

    $foo = $di->get('Foo'); // contains Bar!

---

Zend\\Di Features
----

* Intelligent mix of definition strategies: Runtime, Compiled, Builder
* Annotation support to automate creation of definitions
* Flexible and simple configuration

Presenter Notes
----

Annotation support is primarily to make interface injection possible.

---

Aspect Oriented Design
=======

---

What?
----

* How can we create extensible architectures that allow for a variety of
  cross-cutting concerns?
* How can we create architectures that allow developers to shape workflows and
  execution to suit differing needs?

---

Zend\\EventManager
----

* Trigger *Events* to be handled by *Listeners*

---

It's simple, really
----

    !php
    <?php
    use Zend\EventManager\EventManager;

    $events = new EventManager();
    $events->attach('execute', function($e) {
        $event  = $e->getName();
        $params = json_encode($e->getParams());
        sprintf('%s: %s', $event, $params);
    });
    $events->trigger('execute', null, $params);

---

Features
----

* Listener prioritization
* Static attachment, by context
* Ability to short-circuit 
* Result introspection

---

Dispatch This!
====

Presenter Notes
----

While the DI container and the EventManager provide important tools for the
framework, another big change is a paradigm shift towards simpler processes. One
realization is that a web application is basically about handling HTTP requests
and returning HTTP responses. From this we derived a simple solution: the
Dispatchable interface.

---

Zend\\Stdlib\\Dispatchable
----

    !php
    <?php
    namespace Zend\Stdlib;

    interface Dispatchable
    {
        public function dispatch(
            RequestDescription $request, 
            ResponseDescription $response = null);
    }

Presenter Notes
----

It's so simple and obvious. It can be used by servers to accept requests and
return responses, or by clients, to send requests, and parse responses.

---

Use Cases for Dispatchables
----

* **Strategy/Command patterns**

Presenter Notes
----

Dispatchable is really nothing more than a Strategy Pattern. The whole idea is a
common signature that many objects implement.

---

Use Cases for Dispatchables
----

* Strategy/Command patterns
* **Server classes**

---

Use Cases for Dispatchables
----

* Strategy/Command patterns
* Server classes
* **HTTP clients**

---

Use Cases for Dispatchables
----

* Strategy/Command patterns
* Server classes
* HTTP clients
* **MVC applications**

Presenter Notes
----

One thing interesting about the MVC is that because the same base is used for
server classes and general Strategies or Commands, these, too, can be attached
to the MVC directly, with little or no extra overhead.

---

Modular MVC Applications
====

Presenter Notes
----

We all know the real reason you're here today. You want to see what a ZF2
application looks like.

---

Basics
----

* An `Application` composes a router, a locator, and an event manager
* A matching route should return a controller name
* Controllers are pulled from the locator and dispatched
* Routing and dispatching are _events_

Presenter Notes
----

The last point is incredibly important. It means that if you want to provide
alternate strategies for routing or dispatch, all you need to do is replace
the listeners on those events.

---

Modules
----

* The basic unit in a ZF2 MVC application is a _Module_
* Modules are simply:
  * A **namespace**,
  * containing a single classfile, a **Module**

Presenter Notes
----

* Structure is left to the developer, though we have a _recommended_ structure

---

Example
----

We start with the following directory structure:

    modules/
        Foo/
            Module.php

---

Example (part 2)
----

Which contains:

    !php
    <?php
    namespace Foo;

    class Module { }

Presenter Notes
----

* That's really it. It's a single entry point, and completely opt-in.
* Usually, we define a `getConfig()` method; the bootstrap then merges
  configuration from all modules that support this.

---

Modules usually also provide...
----

* Autoloading artifacts
* Basic configuration

Presenter Notes
----

Commonly, we will provide DI and routing configuration.

---

Example module configuration
----

    !php
    <?php
    return array(
        'routes' => array( /* ... */ ),
        'di'     => array( /* ... */ ),
    );

---

Modules can contain...
----

* PHP code, _including MVC code_
* Assets, such as CSS, JS, and images
* Unit tests
* Documentation
* _Anything_

---

index.php
----

    !php
    <?php
    use Zend\Module\Manager,
        Zend\Mvc\Bootstrap,
        Zend\Mvc\Application;

    $config  = include __DIR__ . '/../configs/app.config.php';
    $modules = new Manager($config['modules']);

    $bootstrap = new Bootstrap($modules);
    $app       = new Application();
    $bootstrap->bootstrap($app);
    $app->run()->send();

Presenter Notes
----

* Loops through modules, grabbing configuration
* Bootstrap 

---

Controllers
----

    !php
    <?php
    namespace Foo\Controller;

    use Zend\Mvc\Controller\ActionController;

    class HelloController extends ActionController
    {
        public function worldAction()
        {
            $query   = $this->request->query();
            $message = $query->get('message', 'Nobody');
            return array('message' => $message);
        }
    }

Presenter Notes
----

* Looks a lot like ZF1
* Returning an array... completely divorced from View
* Inject dependencies via setters or constructor params

---

How is the view rendered?
----

Via an Event Listener:

    !php
    <?php
    use Zend\EventManager\EventCollection as Events,
        Zend\EventManager\ListenerAggregate;

    class ViewListener implements ListenerAggregate
    {
        /* ... */

        public function attach(Events $events)
        {
            $events->attach('dispatch', 
                array($this, 'renderView', -100);
            $events->attach('dispatch', 
                array($this, 'renderLayout', -1000);
        }

        /* ... */
    }

Presenter Notes
----

* Low priority listeners, layout happening magnitudes later than view

---

Getting Dependencies
----

    !php
    <?php
    namespace Contact\Controller;

    use Zend\Mail\Transport,
        Zend\Mvc\Controller\ActionController;

    class ContactController extends ActionController
    {
        public function setMailer(Transport $transport)
        {
            $this->transport = $transport;
        }
    }

Presenter Notes
----

* Dependencies are now typically injected, not pulled

---

DI Configuration
----

    !php
    <?php
    return array('di' => array(
      'definition' => array('class' => array(
        'Zend\Mail\Transport\Smtp' => array(
          '__construct' => array(
            'host' => array('required' => true, 'type' => false),
            'user' => array('required' => true, 'type' => false),
            'pass' => array('required' => true, 'type' => false),
          ),
        ),
      )),
      'instance' => array(
        'Zend\Mail\Transport' => array('parameters' => array(
          'host' => 'some.host.tld', 'user' => 'user', 'pass' => 'pass'
        )),
      ),
    );

Presenter Notes
----

* Shows definitions, as well as basic configuration
* Definitions can be compiled!

---

Wrapping Up
====

---

When?
----

* Beta 1: **Today**
    * http://packages.zendframework.com/

Presenter Notes
----

* We're doing gmail style betas. We *want* you to play with the code. We think
  it's compelling, and going to change the way you develop applications.
* This also means that our betas are going to introduce changes. We'll work hard
  to minimize them, but be prepared. But build with it!

---

When?
----

* New Betas at least every six weeks, until we're done.

Presenter Notes
----

* Next beta is focussing on low-level components such as caching, logging,
  mail, debug, etc.
* Third beta will focus on things like CLI tooling, i18n/l10n, etc.

---

Helping out
----

* http://framework.zend.com/zf2
* http://github.com/zendframework
* Bi-weekly IRC meetings (#zf2-meeting on Freenode IRC)
* \#zftalk.2 on Freenode IRC

Presenter Notes
----

* Read our Git guide, sign our CLA, blah, blah, blah

---

Thank You!
====

* Feedback?
    * http://framework.zend.com/
    * http://twitter.com/weierophinney
    * http://joind.in/????
