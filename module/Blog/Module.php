<?php

namespace Blog;

use InvalidArgumentException,
    Zend\EventManager\StaticEventManager,
    Zend\Module\Consumer\AutoloaderProvider;

class Module implements AutoloaderProvider
{
    public function init()
    {
        $events = StaticEventManager::getInstance();
        $events->attach('bootstrap', 'bootstrap', array($this, 'bootstrap'));
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php'
            ),
        );
    }

    public function getConfig($env = null)
    {
        $config = include __DIR__ . '/config/module.config.php';
        if (null === $env) {
            return $config;
        }

        if (!isset($config[$env])) {
            throw new InvalidArgumentException(sprintf(
                'Unrecognized environment "%s" provided to %s',
                $env,
                __METHOD__
            ));
        }

        return $config[$env];
    }

    public function bootstrap($e)
    {
        $app    = $e->getParam('application');
        $events = $app->events();
        $events->attach('route', array($this, 'registerBlogListener'), -10);
    }

    public function registerBlogListener($e)
    {
        $routeMatch = $e->getRouteMatch();
        if (!$routeMatch) {
            return;
        }

        $matchedRoute = $routeMatch->getMatchedRouteName();
        if ('blog' != substr($matchedRoute, 0, 4)) {
            return;
        }

        // we have something of interest!
        $events   = StaticEventManager::getInstance();
        $listener = new EventListeners\EntryControllerListener();
        $listener->attach($events);
    }

    public function getProvides()
    {
        return array(
            'name'    => 'Blog',
            'version' => '0.1.0',
        );
    }

    public function getDependencies()
    {
        return array(
            'php' => array(
                'required' => true,
                'version'  => '>=5.3.1',
            ),
            'ext/mongo' => array(
                'required' => true,
                'version'  => '>=1.2.0',
            ),
            'CommonResource' => array(
                'required' => true,
                'version'  => '>=0.1.0',
            )
        );
    }
}
