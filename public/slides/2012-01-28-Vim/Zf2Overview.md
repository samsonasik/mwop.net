Dispatch This!
==========

.fx: titleslide

---

Our goal
----

.fx: centeredHeaders

Better consistency and performance.
----

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

* Strategy/Command patterns
* Server classes
* HTTP clients

Presenter Notes
----

Dispatchable is really nothing more than a Strategy Pattern. The whole idea is a
common signature that many objects implement.

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
* We have a few interfaces you can implement that hint to the module manager how
  a module can be _used_

---

Example
----

We start with the following directory structure:

    module/
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

---

Modules usually also provide...
----

* Autoloading artifacts
* Basic configuration

Presenter Notes
----

Commonly, we will provide DI and routing configuration.

---

Providing autoloading hints
----

    !php
    <?php
    namespace Foo;
    use Zend\Module\Consumer\AutoloaderProvider;
    class Module implements AutoloaderProvider
    {
        public function getAutoloaderConfig()
        {
          return array(
            'Zend\Loader\ClassMapAutoloader' => array(
              include __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
              'namespaces' => array(
                'Foo' => __DIR__ . '/src/Foo',
              ),
            ),
          );
        }
    }

Presenter Notes
----

* Basically, returning a configuration array to pass to the AutoloaderFactory.
  This ensures re-use of the same autoloader objects, but also means that the
  AutoloaderListener will be able to cache this configuration for later use.

---

Providing configuration
----

    !php
    <?php
    namespace Foo;

    class Module
    {
        public function getConfig($env = null)
        {
            $config = include __DIR__ 
                    . '/config/module.config.php';
            if (null === $env) {
                return $config;
            }

            if (!isset($config[$env])) {
                return $config; // or throw exception
            }

            return $config[$env];
        }
    }

Presenter Notes
----

* The config listener merges this configuration with the config it stores
  internally.

---

Example module configuration
----

    !php
    <?php
    return array( // likely want to create sections/envs
        'routes' => array( /* ... */ ),
        'di'     => array( /* ... */ ),
    );

---

Post-load initialization
----

    !php
    <?php
    namespace Foo;
    use Zend\EventManager\StaticEventManager,
        Zend\Module\Manager as ModuleManager;
    class Module
    {
        public function init(ModuleManager $manager)
        {
            $events = StaticEventManager::getInstance();
            $events->attach(
                'bootstrap', 'bootstrap', 
                array($this, 'bootstrap')
            );
        }

        public function bootstrap($e)
        {
            $app    = $e->getParam('application');
            // do some stuff...
        }
    }

Presenter Notes
----

* a special "InitTrigger" listener will call init() if it exists
* this is a good place to register events
* shows execution order: module manager aggregates items and triggers events; we
  then execute the bootstrap, which triggers other events; and then we run the
  application
* Event at bootstrap knows about the application, config, and module manager

---

Modules can contain...
----

* PHP code, _including MVC code_
* Assets, such as CSS, JS, and images
* Unit tests
* Documentation
* _Anything_

Presenter Notes
----

I've created a module that simply provided SkeletonCSS in a way that I could
easily drop it into a project. I have modules that are basically libraries, and
contain source code, unit tests, and documentation. The point is, they can
provide just about anything, because the only requirement is the Module class as
an entry point.

---

Putting it together: index.php
----

.fx: smaller-code

    !php
    <?php
    $appConfig = include __DIR__ . '/../config/application.config.php';

    $moduleLoader = new ModuleAutoloader($appConfig['module_paths']);
    $moduleLoader->register();

    $moduleManager = new Manager($appConfig['modules']);
    $moduleManager
        ->getConfigListener()
        ->addConfigGlobPath(__DIR__ . '/config/autoload/*.config.php');
    $moduleManager->loadModules();
    $config = $moduleManager->getMergedConfig();

    // Create application, bootstrap, and run
    $bootstrap   = new Bootstrap($config);
    $application = new Application;
    $bootstrap->bootstrap($application);
    $application->run()->send();

Presenter Notes
----

* Loops through modules, grabbing configuration
* Merges in local configuration (addConfigGlobPath())
* Bootstraps from configuration
* Executes application

---

Under the hood:
----

* Bootstrap
    * Instantiates and configures DI container
    * Instantiates and configures router
    * Triggers "bootstrap" event
* Application
    * Triggers "route" event
    * Triggers "dispatch" event
    * As soon as a response is returned by a listener, sends it

---

Routing
----

.fx: centeredHeaders

The act of matching a Request 

to a Controller.

---

Types of routes
----

* **Literal:** `"/contact"`
* **Segment:** `"/article/:id"`
* **Regex:** `"/tag/(?<tag>[^/]+)"`
* **Part/TreeRouteStack:** tree of routes
* **Wildcard:** `"/*"`
* **Hostname** 
* **Scheme** 

---

TreeRouteStack Example
----

    /blog -- Literal 
        .xml -- Literal ("/blog.xml")
        /(?<id>[^/]+) -- Regex ("/blog/foo")
        /tag/(?<tag>[^/.]+) -- Regex ("/blog/tag/foo")
            .xml -- Literal ("/blog/tag/foo.xml")
        /year/:year -- Segment ("/blog/year/2011")
        /month/:year/:month -- Segment ("/blog/month/2011/12")

Presenter Notes
---
* Any given route can indicate if it can terminate, or if additional segments
  are required
* Any given route may have child routes
* Each route gets full configuration for its type

---

Routing
---

A matched route **MUST** return a controller

    !php
    <?php
    use Zend\Mvc\Router\Http\Regex as RegexRoute;
    $route = new RegexRoute(
        '/blog/(?<id>[/.]+)', 
        '/blog/%id%', 
        array(
            'controller' => 'Blog\Controller\EntryController'
        )
    );

Presenter Notes
---
* Controller is what the Application is trying to _dispatch_
* Can be the class name (recommended) or an alias
* Anything else returned is up to the developer -- "action" is not necessary, as
  your controller defines what to do based on the Request and RouteMatch

---

A note on RouteMatch-es
----

Routes return a `RouteMatch` object on success

    !php
    <?php
    namespace Zend\Mvc\Router;

    class RouteMatch
    {
        public function getMatchedRouteName();
        public function getParams();
        public function getParam($name, $default = null);
    }

Presenter Notes
----
* This allows you to pull out anything matched by the route.
* Routes part of a TreeRoute will contain all matched parameters.

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
            $routeMatch = $this->getEvent()->getRouteMatch()
            $target     = $routeMatch()->getParam('target');
            $post       = $this->request->post();
            $message    = $post->get('message', 'Nobody');
            return array(
                'target' => $target,
                'message' => $message,
            );
        }
    }

Presenter Notes
----

* Looks a lot like ZF1
* Returning an array... completely divorced from View
* Inject dependencies via setters or constructor params
* **_You can simply implement Dispatchable if you want!_**

---

This is also a controller
----

    !php
    <?php
    namespace Foo\Controller;

    use Zend\Stdlib\Dispatchable,
        Zend\Stdlib\RequestDescription as Request,
        Zend\Stdlib\ResponseDescription as Response;

    class MyController implements Dispatchable
    {
        public function dispatch(
            Request $request, Response $response = null
        ) {
            // do something and return a Response object
        }
    }

---

What about the V, View?
----

When is view rendering happening? Via an Event Listener:

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
* We'll be formalizing this later
  * Discuss the concept of a "ViewResult" type of object

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

.fx: smaller-code

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

* Beta 1: 
    * http://packages.zendframework.com/
* Beta 2:
    * Next week
* New Betas at least every six weeks, until we're done.
* Stable:
    * ? _(hopefully April-ish)_

Presenter Notes
----

* We're doing gmail style betas. We *want* you to play with the code. We think
  it's compelling, and going to change the way you develop applications.
* This also means that our betas are going to introduce changes. We'll work hard
  to minimize them, but be prepared. But build with it!
* Beta2 is focussing on low-level components such as caching, logging,
  mail, debug, etc.
* Third beta will focus on things like CLI tooling, i18n/l10n, etc.

---

Resources
----

* Bundle o' links! 
    * http://bit.ly/rzOOge
* The ZF2 subsite
    * http://framework.zend.com/zf2
* My "mwop.net" site repository
    * http://git.mwop.net/?a=summary&p=zf2sandbox

---

Helping out
----

* http://framework.zend.com/zf2
* https://github.com/zendframework
* Bi-weekly IRC meetings (#zf2-meeting on Freenode IRC)
* \#zftalk.2 on Freenode IRC

Presenter Notes
----

* Read our Git guide, NO CLA REQUIRED, blah, blah, blah

---

Thank You!
====

* Feedback?
    * http://framework.zend.com/
    * http://twitter.com/weierophinney
