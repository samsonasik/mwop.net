<?php

namespace Authentication;

use Zend\Config\Config,
    Zend\EventManager\EventCollection,
    Zend\EventManager\ListenerAggregate,
    Zend\Mvc\MvcEvent,
    Zend\View\Model\ViewModel,
    Zend\View\View;

class AuthenticationListener implements ListenerAggregate
{
    protected $auth;
    protected $config;
    protected $events;
    protected $listeners;
    protected $view;

    public function __construct(AuthenticationService $auth, View $view, Config $config, EventCollection $events)
    {
        $this->auth   = $auth->getAuthentication();
        $this->view   = $view;
        $this->config = $config->authentication;
        $this->events = $events;
    }

    public function attach(EventCollection $events)
    {
    }

    public function detach(EventCollection $events)
    {
        foreach ($this->listeners as $key => $listener) {
            $events->detach($listener);
            unset($this->listeners[$key]);
        }
    }

    /**
     * Test if we have an authenticated user
     *
     * Simple test: if we do not have a route match, or if the target's class 
     * is not in our config, we assume it's a valid action.
     *
     * If the target's class is in our config, but the action found in the
     * route match is not in the sub-config, we also assume a valid action;
     * if the action is a '*', we have to verify an identity first.
     *
     * If the target's class is in our config, and the action found in the
     * route match is in the sub-config, we then check if we have an identity
     * in our authentication. If so, we return true.
     *
     * Otherwise, we flag the response as a 401 and stop propagation.
     * 
     * @param  MvcEvent $e 
     * @return true|\Zend\Http\Response
     */
    public function testAuthenticatedUser(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        if (!$routeMatch) {
            return true;
        }

        $action = $routeMatch->getParam('action', false);
        if (!$action) {
            return true;
        }

        $target = $e->getTarget();
        $class  = get_class($target);
        if (!isset($this->config->{$class})) {
            return true;
        }

        $config = $this->config->{$class};
        if (is_string($config))  {
            if ('*' != $action && $config != $action) {
                return true;
            }
        }
        if ($config instanceof Config) {
            $config = $config->toArray();
            if ('*' != $action && !in_array($action, $config)) {
                return true;
            }
        }

        if ($this->auth->hasIdentity()) {
            return true;
        }

        $response = $e->getResponse();
        $response->setStatusCode(401);
        $layout   = $e->getViewModel();
        $model    = new ViewModel();
        $model->setTemplate('authentication/401');
        $layout->addChild($model, 'content');

        $this->events->trigger('render', $e);
        return $response;
    }
}
