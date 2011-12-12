<?php
return new Zend\Config\Config(array(
    'module_paths' => array(
        realpath(__DIR__ . '/../module'),
    ),
    'modules' => array(
        'Application',
        'Authentication',
        'Cache',
        'Comic',
        'CommonResource',
        'Blog',
        'Contact',
    ),
    'module_listener_options' => array( 
        'config_cache_enabled'     => false,
        'cache_dir'                => realpath(dirname(__DIR__) . '/data/cache'),
        'application_environment'  => getenv('APPLICATION_ENV'),
    ),
));
